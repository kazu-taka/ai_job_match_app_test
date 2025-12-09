<?php

declare(strict_types=1);

use App\Models\JobPost;

use function Livewire\Volt\{layout, mount, state, title};

layout('components.layouts.app');
title('求人詳細');

state(['job' => null]);

// 求人データを読み込み
mount(function (JobPost $jobPost) {
    // 認可チェック（誰でも閲覧可能）
    $this->authorize('view', $jobPost);

    // リレーションデータを先読み込み
    $this->job = $jobPost->load(['company', 'location']);
});

// 削除処理
$delete = function () {
    // 認可チェック（自社求人のみ削除可能）
    $this->authorize('delete', $this->job);

    $this->job->delete();

    return $this->redirect(route('jobs.index'), navigate: true);
};

?>

<div>
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        {{-- 戻るボタン --}}
        <div>
            <flux:button :href="route('jobs.index')" wire:navigate variant="ghost" icon="arrow-left">
                求人一覧に戻る
            </flux:button>
        </div>

        {{-- 求人詳細 --}}
        <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            {{-- ヘッダー --}}
            <div class="mb-6">
                <flux:heading size="xl" class="mb-2">{{ $job->title }}</flux:heading>
                <div class="text-lg text-gray-600 dark:text-gray-400">
                    {{ $job->company->name }}
                </div>
            </div>

            {{-- 求人情報グリッド --}}
            <div class="mb-6 grid gap-6 md:grid-cols-2">
                {{-- 勤務地 --}}
                <div>
                    <flux:label class="mb-2">勤務地</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.map-pin class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->location->prefecture }} {{ $job->location->city }}</span>
                    </div>
                </div>

                {{-- 雇用形態 --}}
                <div>
                    <flux:label class="mb-2">雇用形態</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.briefcase class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->employmentType() }}</span>
                    </div>
                </div>

                {{-- 勤務形態 --}}
                <div>
                    <flux:label class="mb-2">勤務形態</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.clock class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->workStyle() }}</span>
                    </div>
                </div>

                {{-- 業種 --}}
                <div>
                    <flux:label class="mb-2">業種</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.building-office class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->industry() }}</span>
                    </div>
                </div>

                {{-- 勤務時間 --}}
                <div>
                    <flux:label class="mb-2">勤務時間</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.clock class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->working_hours }}</span>
                    </div>
                </div>

                {{-- 給与 --}}
                <div>
                    <flux:label class="mb-2">給与</flux:label>
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

                {{-- 募集人数 --}}
                <div>
                    <flux:label class="mb-2">募集人数</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.users class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        <span>{{ $job->number_of_positions }}名</span>
                    </div>
                </div>

                {{-- 募集期限 --}}
                <div>
                    <flux:label class="mb-2">募集期限</flux:label>
                    <div class="flex items-start gap-2">
                        <flux:icon.calendar class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                        @if ($job->expires_at)
                            <span>{{ $job->expires_at->format('Y年m月d日') }}</span>
                        @else
                            <span class="text-gray-500">期限なし</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 詳細内容 --}}
            <div class="mb-6">
                <flux:label class="mb-2">詳細内容</flux:label>
                <div
                    class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900 whitespace-pre-wrap">
                    {{ $job->description }}
                </div>
            </div>

            {{-- 投稿日時 --}}
            <div class="text-sm text-gray-500 dark:text-gray-400">
                投稿日時: {{ $job->posted_at->format('Y年m月d日 H:i') }}
            </div>
        </div>

        {{-- アクションボタン --}}
        <div class="flex justify-between">
            <div>
                @if (Auth::user()->role === 'worker')
                    {{-- ワーカーユーザー: 応募ボタン --}}
                    <flux:button variant="primary" disabled>
                        応募する（準備中）
                    </flux:button>
                @endif
            </div>

            <div class="flex gap-2">
                @can('update', $job)
                    {{-- 企業ユーザー（自社求人のみ）: 編集ボタン --}}
                    <flux:button :href="route('jobs.edit', $job)" wire:navigate variant="ghost">
                        編集
                    </flux:button>
                @endcan

                @can('delete', $job)
                    {{-- 企業ユーザー（自社求人のみ）: 削除ボタン --}}
                    <flux:modal.trigger name="delete-confirmation">
                        <flux:button variant="danger">
                            削除
                        </flux:button>
                    </flux:modal.trigger>
                @endcan
            </div>
        </div>

        {{-- 削除確認モーダル --}}
        <flux:modal name="delete-confirmation" class="max-w-md">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">求人を削除しますか?</flux:heading>
                    <flux:text class="mt-2">
                        この操作は取り消せません。本当に削除してもよろしいですか？
                    </flux:text>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">
                            キャンセル
                        </flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="delete" variant="danger">
                        削除する
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>
