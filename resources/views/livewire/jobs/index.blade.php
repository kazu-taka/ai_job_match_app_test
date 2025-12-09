<?php

declare(strict_types=1);

use App\Models\Code;
use App\Models\JobPost;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.app');
title('求人一覧');

// 検索・フィルタ状態
state([
    'search' => '',
    'selectedPrefecture' => null,
    'location_id' => null,
    'employment_types' => [],
    'work_styles' => [],
    'industries' => [],
]);

// 都道府県リストを取得
$prefectures = computed(function () {
    return Location::query()->whereNull('city')->orderBy('code')->get();
});

// 選択された都道府県に対応する市区町村リストを取得
$cities = computed(function () {
    if ($this->selectedPrefecture === null) {
        return collect();
    }

    $prefecture = Location::query()->find($this->selectedPrefecture);
    if (!$prefecture) {
        return collect();
    }

    return Location::query()->where('prefecture', $prefecture->prefecture)->whereNotNull('city')->orderBy('code')->get();
});

// 雇用形態リストを取得
$employmentTypeOptions = computed(function () {
    return Code::query()->where('type', 1)->orderBy('sort_order')->get();
});

// 勤務形態リストを取得
$workStyleOptions = computed(function () {
    return Code::query()->where('type', 2)->orderBy('sort_order')->get();
});

// 業種リストを取得
$industryOptions = computed(function () {
    return Code::query()->where('type', 3)->orderBy('sort_order')->get();
});

// 都道府県変更時の処理
$updatedSelectedPrefecture = function () {
    // 都道府県を変更したら市区町村の選択をリセット
    $this->location_id = null;
};

// 求人一覧を取得（検索・フィルタ適用）
$jobs = computed(function () {
    return JobPost::query()
        ->with(['company', 'location'])
        ->when($this->search, function (Builder $query) {
            $query->where(function (Builder $q) {
                $q->where('title', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->location_id, function (Builder $query) {
            // 市区町村が選択されている場合は、その市区町村で絞り込み
            $query->where('location_id', $this->location_id);
        })
        ->when($this->selectedPrefecture && !$this->location_id, function (Builder $query) {
            // 都道府県のみ選択されている場合は、その都道府県内のすべての市区町村で絞り込み
            $prefecture = Location::query()->find($this->selectedPrefecture);
            if ($prefecture) {
                $locationIds = Location::query()->where('prefecture', $prefecture->prefecture)->whereNotNull('city')->pluck('id');
                $query->whereIn('location_id', $locationIds);
            }
        })
        ->when(!empty($this->employment_types), function (Builder $query) {
            $query->whereIn('employment_type_id', $this->employment_types);
        })
        ->when(!empty($this->work_styles), function (Builder $query) {
            $query->whereIn('work_style_id', $this->work_styles);
        })
        ->when(!empty($this->industries), function (Builder $query) {
            $query->whereIn('industry_id', $this->industries);
        })
        ->latest('posted_at')
        ->paginate(20);
});

// フィルタリセット処理
$resetFilters = function () {
    $this->search = '';
    $this->selectedPrefecture = null;
    $this->location_id = null;
    $this->employment_types = [];
    $this->work_styles = [];
    $this->industries = [];
};

?>

<div>
    <div class="mx-auto max-w-7xl space-y-6 p-6">
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between">
            <flux:heading size="xl">求人一覧</flux:heading>
            @if (Auth::user()->role === 'company')
                <flux:button variant="primary" disabled>
                    新規求人投稿（準備中）
                </flux:button>
            @endif
        </div>

        {{-- 検索・フィルタエリア --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="space-y-4">
                {{-- キーワード検索 --}}
                <flux:field>
                    <flux:label>キーワード検索</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model.live.debounce.300ms="search" type="text" placeholder="求人タイトルや詳細で検索..."
                            class="flex-1" />
                    </div>
                </flux:field>

                {{-- 勤務地フィルタ --}}
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>勤務地（都道府県）</flux:label>
                        <flux:select wire:model.live="selectedPrefecture" placeholder="選択しない">
                            @foreach ($this->prefectures as $prefecture)
                                <option value="{{ $prefecture->id }}">{{ $prefecture->prefecture }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>勤務地（市区町村）</flux:label>
                        <flux:select wire:model.live="location_id" placeholder="選択しない"
                            :disabled="$selectedPrefecture === null">
                            @foreach ($this->cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                {{-- 雇用形態フィルタ --}}
                <flux:field>
                    <flux:label>雇用形態</flux:label>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($this->employmentTypeOptions as $option)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.live="employment_types" value="{{ $option->type_id }}"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span>{{ $option->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </flux:field>

                {{-- 勤務形態フィルタ --}}
                <flux:field>
                    <flux:label>勤務形態</flux:label>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($this->workStyleOptions as $option)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.live="work_styles" value="{{ $option->type_id }}"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span>{{ $option->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </flux:field>

                {{-- 業種フィルタ --}}
                <flux:field>
                    <flux:label>業種</flux:label>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($this->industryOptions as $option)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.live="industries" value="{{ $option->type_id }}"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span>{{ $option->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </flux:field>

                {{-- フィルタリセット --}}
                <div class="flex justify-end">
                    <flux:button wire:click="resetFilters" variant="ghost">
                        フィルタをリセット
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- 求人一覧 --}}
        <div class="space-y-4">
            {{-- 件数表示 --}}
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span wire:loading.remove
                    wire:target="search,selectedPrefecture,location_id,employment_types,work_styles,industries">
                    {{ $this->jobs->total() }}件の求人が見つかりました
                </span>
                <span wire:loading
                    wire:target="search,selectedPrefecture,location_id,employment_types,work_styles,industries">
                    検索中...
                </span>
            </div>

            {{-- 求人カード一覧 --}}
            @if ($this->jobs->count() > 0)
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->jobs as $job)
                        <a href="#" wire:navigate
                            class="block rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            {{-- 求人タイトル --}}
                            <flux:heading size="lg" class="mb-2">{{ $job->title }}</flux:heading>

                            {{-- 企業名 --}}
                            <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $job->company->name }}
                            </div>

                            {{-- 求人情報 --}}
                            <div class="space-y-2 text-sm">
                                {{-- 勤務地 --}}
                                <div class="flex items-start gap-2">
                                    <flux:icon.map-pin class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                    <span>{{ $job->location->prefecture }} {{ $job->location->city }}</span>
                                </div>

                                {{-- 雇用形態 --}}
                                <div class="flex items-start gap-2">
                                    <flux:icon.briefcase class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                    <span>{{ $job->employmentType() }}</span>
                                </div>

                                {{-- 給与 --}}
                                <div class="flex items-start gap-2">
                                    <flux:icon.currency-yen class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                    <span>
                                        @if (in_array($job->employment_type_id, [1, 2]))
                                            月給 {{ number_format($job->salary) }}円
                                        @else
                                            時給 {{ number_format($job->salary) }}円
                                        @endif
                                    </span>
                                </div>
                            </div>

                            {{-- 募集期限 --}}
                            @if ($job->expires_at)
                                <div class="mt-4 text-xs text-red-600 dark:text-red-400">
                                    募集期限: {{ $job->expires_at->format('Y年m月d日') }}
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>

                {{-- ページネーション --}}
                <div class="mt-6">
                    {{ $this->jobs->links() }}
                </div>
            @else
                {{-- 求人が見つからない場合 --}}
                <div
                    class="rounded-lg border border-gray-200 bg-gray-50 p-12 text-center dark:border-gray-700 dark:bg-gray-900">
                    <flux:icon.magnifying-glass class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                    <flux:heading size="lg" class="mb-2">求人が見つかりませんでした</flux:heading>
                    <flux:text class="text-gray-600 dark:text-gray-400">
                        検索条件を変更して再度お試しください。
                    </flux:text>
                </div>
            @endif
        </div>
    </div>
</div>
