<?php

declare(strict_types=1);

use App\Http\Requests\StoreJobApplicationRequest;
use App\Mail\ApplicationReceivedMail;
use App\Models\JobApplication;
use App\Models\JobPost;
use Illuminate\Support\Facades\Mail;

use function Livewire\Volt\{layout, mount, rules, state, title};

layout('components.layouts.app');
title('求人への応募');

state(['job' => null, 'motive' => '']);

// バリデーションルール
rules([
    'motive' => ['nullable', 'string', 'max:1000'],
]);

// 求人データを読み込み
mount(function (JobPost $jobPost) {
    // 認可チェック（ワーカーのみ応募可能）
    $this->authorize('create', JobApplication::class);

    // 重複応募チェック
    $existingApplication = JobApplication::query()
        ->where('job_id', $jobPost->id)
        ->where('worker_id', auth()->id())
        ->exists();

    if ($existingApplication) {
        session()->flash('error', 'この求人には既に応募済みです。');

        return $this->redirect(route('jobs.show', $jobPost), navigate: true);
    }

    // リレーションデータを先読み込み
    $this->job = $jobPost->load(['company', 'location']);
});

// 応募処理
$apply = function () {
    // バリデーション
    $this->validate();

    // 重複応募チェック（念のため再確認）
    $existingApplication = JobApplication::query()
        ->where('job_id', $this->job->id)
        ->where('worker_id', auth()->id())
        ->exists();

    if ($existingApplication) {
        session()->flash('error', 'この求人には既に応募済みです。');

        return $this->redirect(route('jobs.show', $this->job), navigate: true);
    }

    // 応募データを登録
    $application = JobApplication::create([
        'job_id' => $this->job->id,
        'worker_id' => auth()->id(),
        'motive' => $this->motive,
        'status' => 'applied',
        'applied_at' => now(),
    ]);

    // リレーションを先読み込み
    $application->load(['jobPost.company', 'worker']);

    // 企業に応募通知メールを送信
    Mail::to($application->jobPost->company->email)->send(new ApplicationReceivedMail($application));

    session()->flash('success', '応募が完了しました。');

    return $this->redirect(route('jobs.show', $this->job), navigate: true);
};

?>

<div>
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        {{-- 戻るボタン --}}
        <div>
            <flux:button :href="route('jobs.show', $job)" wire:navigate variant="ghost" icon="arrow-left">
                求人詳細に戻る
            </flux:button>
        </div>

        {{-- ページタイトル --}}
        <div>
            <flux:heading size="xl">求人への応募</flux:heading>
            <flux:text class="mt-2 text-gray-600 dark:text-gray-400">
                以下の求人に応募します。志望動機を入力してください（任意）。
            </flux:text>
        </div>

        {{-- 求人情報サマリー --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <flux:heading size="lg" class="mb-4">{{ $job->title }}</flux:heading>

            <div class="grid gap-4 md:grid-cols-2">
                {{-- 企業名 --}}
                <div>
                    <flux:label class="mb-1">企業名</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.building-office class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->company->name }}</span>
                    </div>
                </div>

                {{-- 勤務地 --}}
                <div>
                    <flux:label class="mb-1">勤務地</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.map-pin class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->location->prefecture }} {{ $job->location->city }}</span>
                    </div>
                </div>

                {{-- 雇用形態 --}}
                <div>
                    <flux:label class="mb-1">雇用形態</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.briefcase class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->employmentType() }}</span>
                    </div>
                </div>

                {{-- 給与 --}}
                <div>
                    <flux:label class="mb-1">給与</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.currency-yen class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>
                            @if (in_array($job->employment_type_id, [1, 2]))
                                月給 {{ number_format($job->salary) }}円
                            @else
                                時給 {{ number_format($job->salary) }}円
                            @endif
                        </span>
                    </div>
                </div>

                {{-- 勤務時間 --}}
                <div>
                    <flux:label class="mb-1">勤務時間</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.clock class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->working_hours }}</span>
                    </div>
                </div>

                {{-- 募集人数 --}}
                <div>
                    <flux:label class="mb-1">募集人数</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.users class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->number_of_positions }}名</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 応募フォーム --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <form wire:submit="apply" class="space-y-6">
                {{-- 志望動機 --}}
                <flux:field>
                    <flux:label for="motive">志望動機（任意）</flux:label>
                    <flux:textarea wire:model="motive" id="motive" rows="8"
                        placeholder="この求人に応募する理由や、あなたの経験・スキルがどのように活かせるかなど、自由に記入してください。">{{ $motive }}
                    </flux:textarea>
                    <flux:error name="motive" />
                    <flux:description>
                        1000文字以内で入力してください。
                    </flux:description>
                </flux:field>

                {{-- ボタン --}}
                <div class="flex justify-end gap-2">
                    <flux:button :href="route('jobs.show', $job)" wire:navigate variant="ghost">
                        キャンセル
                    </flux:button>

                    <flux:modal.trigger name="apply-confirmation">
                        <flux:button type="button" variant="primary">
                            応募する
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            </form>
        </div>

        {{-- 応募確認モーダル --}}
        <flux:modal name="apply-confirmation" class="max-w-md">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">応募確認</flux:heading>
                    <flux:text class="mt-2">
                        本当にこの求人に応募しますか？<br>
                        応募後、企業からの連絡をお待ちください。
                    </flux:text>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">
                            キャンセル
                        </flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="apply" variant="primary">
                        応募する
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>
