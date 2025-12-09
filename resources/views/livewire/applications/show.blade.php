<?php

declare(strict_types=1);

use App\Models\JobApplication;
use function Livewire\Volt\{state, computed, layout, title, mount};

layout('components.layouts.app');
title('応募詳細');

// 状態定義
state(['jobApplication']);

// 初期化処理
mount(function (JobApplication $jobApplication) {
    $this->authorize('view', $jobApplication);

    // リレーションを先読み込み
    $this->jobApplication = $jobApplication->load(['jobPost.company', 'jobPost.location', 'worker']);
});

// ステータスの日本語表示
$getStatusLabel = function (string $status): string {
    return match ($status) {
        'applied' => '応募中',
        'accepted' => '承認',
        'rejected' => '不承認',
        'declined' => '辞退',
        default => $status,
    };
};

// ステータスバッジの色
$getStatusColor = function (string $status): string {
    return match ($status) {
        'applied' => 'blue',
        'accepted' => 'green',
        'rejected' => 'red',
        'declined' => 'gray',
        default => 'gray',
    };
};

// 給与表示（雇用形態IDから判定）
$formatSalary = function (int $salary, int $employmentTypeId): string {
    // employment_type_id: 1=正社員、2=契約社員、3=パート、4=アルバイト
    // 1,2は月給、3,4は時給
    if (in_array($employmentTypeId, [1, 2])) {
        return number_format($salary) . '円/月';
    }

    return number_format($salary) . '円/時';
};

// 辞退処理
$decline = function () {
    $this->authorize('update', $this->jobApplication);

    // status='applied'の場合のみ辞退可能
    if ($this->jobApplication->status !== 'applied') {
        session()->flash('error', '辞退できるのは応募中のみです。');

        return;
    }

    // ステータスを更新
    $this->jobApplication->update([
        'status' => 'declined',
        'declined_at' => now(),
    ]);

    session()->flash('success', '応募を辞退しました。');

    return $this->redirect(route('applications.index'), navigate: true);
};

?>

<div>
    <flux:header class="mb-6">
        <flux:heading size="xl">応募詳細</flux:heading>
        <flux:subheading>応募情報の詳細を確認できます。</flux:subheading>
    </flux:header>

    {{-- 応募情報カード --}}
    <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">応募情報</h2>

        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="font-medium text-gray-700 dark:text-gray-300">ステータス:</span>
                <flux:badge :color="$this->getStatusColor($jobApplication->status)">
                    {{ $this->getStatusLabel($jobApplication->status) }}
                </flux:badge>
            </div>

            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">応募日:</span>
                <span class="text-gray-600 dark:text-gray-400">
                    {{ $jobApplication->applied_at->format('Y年m月d日 H:i') }}
                </span>
            </div>

            @if ($jobApplication->judged_at)
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">判定日:</span>
                    <span class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->judged_at->format('Y年m月d日 H:i') }}
                    </span>
                </div>
            @endif

            @if ($jobApplication->declined_at)
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">辞退日:</span>
                    <span class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->declined_at->format('Y年m月d日 H:i') }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- 求人情報カード --}}
    <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">求人情報</h2>

        <div class="space-y-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $jobApplication->jobPost->title }}
                </h3>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">企業名:</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->jobPost->company->name }}
                    </p>
                </div>

                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">雇用形態:</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->jobPost->employmentType() }}
                    </p>
                </div>

                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">勤務形態:</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->jobPost->workStyle() }}
                    </p>
                </div>

                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">業種:</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->jobPost->industry() }}
                    </p>
                </div>

                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">勤務地:</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->jobPost->location->prefecture }}
                        @if ($jobApplication->jobPost->location->city)
                            {{ $jobApplication->jobPost->location->city }}
                        @endif
                    </p>
                </div>

                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">給与:</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $this->formatSalary($jobApplication->jobPost->salary, $jobApplication->jobPost->employment_type_id) }}
                    </p>
                </div>

                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">勤務時間:</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->jobPost->working_hours }}
                    </p>
                </div>

                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">募集人数:</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->jobPost->number_of_positions }}名
                    </p>
                </div>
            </div>

            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">詳細内容:</span>
                <p class="mt-2 whitespace-pre-wrap text-gray-600 dark:text-gray-400">
                    {{ $jobApplication->jobPost->description }}</p>
            </div>

            @if ($jobApplication->jobPost->expires_at)
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">募集期限:</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $jobApplication->jobPost->expires_at->format('Y年m月d日') }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- 志望動機カード --}}
    <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">志望動機</h2>

        @if ($jobApplication->motive)
            <p class="whitespace-pre-wrap text-gray-600 dark:text-gray-400">{{ $jobApplication->motive }}</p>
        @else
            <p class="text-gray-500 dark:text-gray-500">記入なし</p>
        @endif
    </div>

    {{-- アクションボタン --}}
    <div class="flex flex-wrap gap-4">
        {{-- 戻るボタン --}}
        <flux:button href="{{ route('applications.index') }}" wire:navigate variant="ghost">
            <flux:icon.arrow-left class="size-5" />
            応募一覧に戻る
        </flux:button>

        {{-- 辞退ボタン（ワーカーのみ、status='applied'の場合のみ表示） --}}
        @if (auth()->user()->role === 'worker' && $jobApplication->status === 'applied')
            <flux:modal.trigger name="decline-confirmation">
                <flux:button variant="danger">
                    辞退する
                </flux:button>
            </flux:modal.trigger>
        @endif
    </div>

    {{-- 辞退確認モーダル --}}
    <flux:modal name="decline-confirmation" class="space-y-6">
        <div class="space-y-4">
            <flux:heading size="lg">応募の辞退</flux:heading>

            <p class="text-gray-600 dark:text-gray-400">
                本当にこの応募を辞退しますか？<br>
                辞退後は取り消すことができません。
            </p>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">
                        キャンセル
                    </flux:button>
                </flux:modal.close>
                <flux:button wire:click="decline" variant="danger">
                    辞退する
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
