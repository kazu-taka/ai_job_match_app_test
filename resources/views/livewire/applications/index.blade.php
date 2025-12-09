<?php

declare(strict_types=1);

use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use function Livewire\Volt\{state, computed, layout, title, mount};

layout('components.layouts.app');
title('応募一覧');

// 検索・フィルタ用の状態
state([
    'search' => '',
    'statusFilters' => [],
    'sortOrder' => 'desc', // desc=新しい順, asc=古い順
]);

// 認可チェック
mount(function () {
    $this->authorize('viewAny', JobApplication::class);
});

// 応募一覧の取得（computed）
$applications = computed(function (): LengthAwarePaginator {
    $query = JobApplication::query()
        ->where('worker_id', auth()->id())
        ->with(['jobPost.company', 'jobPost.location']);

    // キーワード検索（求人タイトル、企業名）
    if ($this->search !== '') {
        $query->whereHas('jobPost', function (Builder $q) {
            $q->where('title', 'like', "%{$this->search}%")->orWhereHas('company', function (Builder $q2) {
                $q2->where('name', 'like', "%{$this->search}%");
            });
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

// 給与表示（雇用形態IDから判定）
$formatSalary = function (int $salary, int $employmentTypeId): string {
    // employment_type_id: 1=正社員、2=契約社員、3=パート、4=アルバイト
    // 1,2は月給、3,4は時給
    if (in_array($employmentTypeId, [1, 2])) {
        return number_format($salary) . '円/月';
    }

    return number_format($salary) . '円/時';
};

?>

<div>
    <flux:header class="mb-6">
        <flux:heading size="xl">応募一覧</flux:heading>
        <flux:subheading>あなたの応募履歴を確認できます。</flux:subheading>
    </flux:header>

    {{-- 検索・フィルタエリア --}}
    <div class="mb-6 space-y-4 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        {{-- キーワード検索 --}}
        <flux:field>
            <flux:label>キーワード検索</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" type="text" placeholder="求人タイトルまたは企業名で検索..." />
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
                    {{-- 左側：求人情報 --}}
                    <div class="flex-1">
                        <div class="mb-2 flex items-start gap-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $application->jobPost->title }}
                            </h3>
                            <flux:badge :color="$this->getStatusColor($application->status)" size="sm">
                                {{ $this->getStatusLabel($application->status) }}
                            </flux:badge>
                        </div>

                        <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <p>
                                <flux:icon.building-office-2 class="inline-block h-4 w-4" />
                                {{ $application->jobPost->company->name }}
                            </p>
                            <p>
                                <flux:icon.map-pin class="inline-block h-4 w-4" />
                                {{ $application->jobPost->location->prefecture }}
                                @if ($application->jobPost->location->city)
                                    {{ $application->jobPost->location->city }}
                                @endif
                            </p>
                            <p>
                                <flux:icon.briefcase class="inline-block h-4 w-4" />
                                {{ $application->jobPost->employmentType() }}
                            </p>
                            <p>
                                <flux:icon.currency-yen class="inline-block h-4 w-4" />
                                {{ $this->formatSalary($application->jobPost->salary, $application->jobPost->employment_type_id) }}
                            </p>
                        </div>
                    </div>

                    {{-- 右側：日付とアクション --}}
                    <div class="flex flex-col items-end gap-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p>応募日: {{ $application->applied_at->format('Y年m月d日') }}</p>
                            @if ($application->judged_at)
                                <p>判定日: {{ $application->judged_at->format('Y年m月d日') }}</p>
                            @endif
                        </div>

                        <flux:button href="{{ route('applications.show', $application) }}" wire:navigate
                            size="sm">
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
                <flux:button href="{{ route('jobs.index') }}" wire:navigate class="mt-4" variant="primary">
                    求人を探す
                </flux:button>
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
