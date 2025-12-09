<?php

declare(strict_types=1);

use App\Models\JobApplication;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use function Livewire\Volt\{state, computed, layout, title, mount};

layout('components.layouts.app');
title('応募管理');

// 検索・フィルタ用の状態
state([
    'selectedJobId' => 'all', // 'all' または JobPost ID
    'search' => '',
    'statusFilters' => [],
    'sortOrder' => 'desc', // desc=新しい順, asc=古い順
]);

// 認可チェック
mount(function () {
    $this->authorize('viewAny', JobApplication::class);
});

// 自社の求人一覧を取得（computed）
$jobPosts = computed(function () {
    return JobPost::query()
        ->where('company_id', auth()->id())
        ->orderBy('posted_at', 'desc')
        ->get();
});

// 応募一覧の取得（computed）
$applications = computed(function (): LengthAwarePaginator {
    $query = JobApplication::query()
        ->whereHas('jobPost', function (Builder $q) {
            $q->where('company_id', auth()->id());
        })
        ->with(['jobPost', 'worker']);

    // 求人フィルタ
    if ($this->selectedJobId !== 'all') {
        $query->where('job_id', $this->selectedJobId);
    }

    // キーワード検索（ワーカー名）
    if ($this->search !== '') {
        $query->whereHas('worker', function (Builder $q) {
            $q->where('name', 'like', "%{$this->search}%");
        });
    }

    // ステータスフィルタ
    if (count($this->statusFilters) > 0) {
        $query->whereIn('status', $this->statusFilters);
    }

    // 並び替え
    $query->orderBy('applied_at', $this->sortOrder);

    return $query->paginate(20);
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

?>

<div>
    <flux:header class="mb-6">
        <flux:heading size="xl">応募管理</flux:heading>
        <flux:subheading>自社求人への応募を管理できます。</flux:subheading>
    </flux:header>

    {{-- 検索・フィルタエリア --}}
    <div class="mb-6 space-y-4 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        {{-- 求人フィルタ --}}
        <flux:field>
            <flux:label>求人で絞り込み</flux:label>
            <select wire:model.live="selectedJobId"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                <option value="all">すべての求人</option>
                @foreach ($this->jobPosts as $jobPost)
                    <option value="{{ $jobPost->id }}">{{ $jobPost->title }}</option>
                @endforeach
            </select>
        </flux:field>

        {{-- キーワード検索 --}}
        <flux:field>
            <flux:label>ワーカー名で検索</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" type="text" placeholder="ワーカー名で検索..." />
        </flux:field>

        {{-- ステータスフィルタ --}}
        <flux:field>
            <flux:label>ステータス</flux:label>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="statusFilters" value="applied"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>応募中</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="statusFilters" value="accepted"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>承認</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="statusFilters" value="rejected"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>不承認</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="statusFilters" value="declined"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>辞退</span>
                </label>
            </div>
        </flux:field>

        {{-- 並び替え --}}
        <flux:field>
            <flux:label>並び替え</flux:label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2">
                    <input type="radio" wire:model.live="sortOrder" value="desc"
                        class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>新しい順</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" wire:model.live="sortOrder" value="asc"
                        class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>古い順</span>
                </label>
            </div>
        </flux:field>
    </div>

    {{-- 応募一覧 --}}
    <div class="space-y-4">
        @forelse ($this->applications as $application)
            <div class="rounded-lg bg-white p-6 shadow transition hover:shadow-lg dark:bg-gray-800"
                wire:key="application-{{ $application->id }}">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    {{-- 左側：応募情報 --}}
                    <div class="flex-1">
                        {{-- ワーカー名とステータス --}}
                        <div class="mb-2 flex items-start gap-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $application->worker->name }}
                            </h3>
                            <flux:badge :color="$this->getStatusColor($application->status)" size="sm">
                                {{ $this->getStatusLabel($application->status) }}
                            </flux:badge>
                        </div>

                        {{-- 求人タイトル（「すべて」の場合のみ表示） --}}
                        @if ($selectedJobId === 'all')
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                                <flux:icon.briefcase class="inline-block h-4 w-4" />
                                求人: {{ $application->jobPost->title }}
                            </div>
                        @endif

                        <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <p>
                                <flux:icon.calendar class="inline-block h-4 w-4" />
                                応募日: {{ $application->applied_at->format('Y年m月d日') }}
                            </p>
                            @if ($application->judged_at)
                                <p>
                                    <flux:icon.check-circle class="inline-block h-4 w-4" />
                                    判定日: {{ $application->judged_at->format('Y年m月d日') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- 右側：アクション --}}
                    <div class="flex items-end">
                        <flux:button href="{{ route('applications.show', $application) }}" wire:navigate size="sm">
                            詳細を見る
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                <flux:icon.magnifying-glass class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                <p class="text-gray-600 dark:text-gray-400">
                    応募が見つかりませんでした。
                </p>
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    @if ($this->applications->hasPages())
        <div class="mt-6">
            {{ $this->applications->links() }}
        </div>
    @endif
</div>
