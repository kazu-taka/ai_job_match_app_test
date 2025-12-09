<?php

declare(strict_types=1);

use App\Http\Requests\StoreJobPostRequest;
use App\Models\Code;
use App\Models\JobPost;
use App\Models\Location;

use function Livewire\Volt\{computed, layout, state, title, updated};

layout('components.layouts.app');
title('求人登録');

state([
    'title' => '',
    'description' => '',
    'employment_type_id' => null,
    'work_style_id' => null,
    'industry_id' => null,
    'prefecture_id' => null,
    'location_id' => null,
    'working_hours' => '',
    'salary' => null,
    'number_of_positions' => null,
    'expires_at' => null,
]);

// 都道府県リストを取得
$prefectures = computed(function () {
    return Location::query()->whereNull('city')->orderBy('code')->get();
});

// 選択された都道府県に対応する市区町村リストを取得
$cities = computed(function () {
    if ($this->prefecture_id === null) {
        return collect();
    }

    $prefecture = Location::query()->find($this->prefecture_id);
    if (!$prefecture) {
        return collect();
    }

    return Location::query()->where('prefecture', $prefecture->prefecture)->whereNotNull('city')->orderBy('code')->get();
});

// 雇用形態リストを取得
$employmentTypes = computed(function () {
    return Code::query()->where('type', 1)->orderBy('sort_order')->get();
});

// 勤務形態リストを取得
$workStyles = computed(function () {
    return Code::query()->where('type', 2)->orderBy('sort_order')->get();
});

// 業種リストを取得
$industries = computed(function () {
    return Code::query()->where('type', 3)->orderBy('sort_order')->get();
});

// 都道府県変更時の処理
updated([
    'prefecture_id' => function () {
        // 都道府県を変更したら市区町村の選択をリセット
        $this->location_id = null;
    },
]);

// 登録処理
$store = function () {
    // 認可チェック（企業ユーザーのみ）
    $this->authorize('create', JobPost::class);

    // バリデーション
    $validated = $this->validate(new StoreJobPostRequest()->rules());

    // 求人情報を登録
    JobPost::create([
        'company_id' => auth()->id(),
        'title' => $validated['title'],
        'description' => $validated['description'],
        'employment_type_id' => $validated['employment_type_id'],
        'work_style_id' => $validated['work_style_id'],
        'industry_id' => $validated['industry_id'],
        'location_id' => $validated['location_id'],
        'working_hours' => $validated['working_hours'],
        'salary' => $validated['salary'],
        'number_of_positions' => $validated['number_of_positions'],
        'posted_at' => now(),
        'expires_at' => $validated['expires_at'] ? \Carbon\Carbon::parse($validated['expires_at']) : null,
    ]);

    // 求人一覧画面にリダイレクト
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

        {{-- 求人登録フォーム --}}
        <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <flux:heading size="xl" class="mb-6">新規求人投稿</flux:heading>

            <form wire:submit="store" class="space-y-6">
                {{-- 求人タイトル --}}
                <flux:field>
                    <flux:label>求人タイトル<span class="text-red-600">*</span></flux:label>
                    <flux:input wire:model="title" type="text" placeholder="例：Webアプリケーションエンジニア募集" />
                    <flux:error name="title" />
                </flux:field>

                {{-- 詳細内容 --}}
                <flux:field>
                    <flux:label>詳細内容<span class="text-red-600">*</span></flux:label>
                    <flux:textarea wire:model="description" rows="5"
                        placeholder="例：PHP/Laravelを使用した自社サービス開発。フルリモート可能。技術力を高めたい方歓迎！">
                    </flux:textarea>
                    <flux:error name="description" />
                </flux:field>

                {{-- 雇用形態・勤務形態 --}}
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:field>
                        <flux:label>雇用形態<span class="text-red-600">*</span></flux:label>
                        <select wire:model="employment_type_id"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                            <option value="">選択してください</option>
                            @foreach ($this->employmentTypes as $type)
                                <option value="{{ $type->type_id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <flux:error name="employment_type_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>勤務形態<span class="text-red-600">*</span></flux:label>
                        <select wire:model="work_style_id"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                            <option value="">選択してください</option>
                            @foreach ($this->workStyles as $style)
                                <option value="{{ $style->type_id }}">{{ $style->name }}</option>
                            @endforeach
                        </select>
                        <flux:error name="work_style_id" />
                    </flux:field>
                </div>

                {{-- 業種 --}}
                <flux:field>
                    <flux:label>業種<span class="text-red-600">*</span></flux:label>
                    <select wire:model="industry_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">選択してください</option>
                        @foreach ($this->industries as $industry)
                            <option value="{{ $industry->type_id }}">{{ $industry->name }}</option>
                        @endforeach
                    </select>
                    <flux:error name="industry_id" />
                </flux:field>

                {{-- 勤務地（2段階選択） --}}
                <div class="space-y-4">
                    <flux:label>勤務地<span class="text-red-600">*</span></flux:label>
                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:field>
                            <flux:label>都道府県</flux:label>
                            <select wire:model.live="prefecture_id"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                <option value="">選択してください</option>
                                @foreach ($this->prefectures as $prefecture)
                                    <option value="{{ $prefecture->id }}">{{ $prefecture->prefecture }}</option>
                                @endforeach
                            </select>
                        </flux:field>

                        <flux:field>
                            <flux:label>市区町村</flux:label>
                            <select wire:model="location_id" @disabled($prefecture_id === null)
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                <option value="">選択してください</option>
                                @foreach ($this->cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->city }}</option>
                                @endforeach
                            </select>
                        </flux:field>
                    </div>
                    <flux:error name="location_id" />
                </div>

                {{-- 勤務時間 --}}
                <flux:field>
                    <flux:label>勤務時間<span class="text-red-600">*</span></flux:label>
                    <flux:input wire:model="working_hours" type="text" placeholder="例：9:00-18:00" />
                    <flux:error name="working_hours" />
                </flux:field>

                {{-- 給与・募集人数 --}}
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:field>
                        <flux:label>給与（円）<span class="text-red-600">*</span></flux:label>
                        <flux:input wire:model="salary" type="number" min="0" placeholder="例：300000" />
                        <flux:description>
                            正社員・契約社員：月給、パート・アルバイト：時給
                        </flux:description>
                        <flux:error name="salary" />
                    </flux:field>

                    <flux:field>
                        <flux:label>募集人数<span class="text-red-600">*</span></flux:label>
                        <flux:input wire:model="number_of_positions" type="number" min="1" placeholder="例：2" />
                        <flux:error name="number_of_positions" />
                    </flux:field>
                </div>

                {{-- 募集期限 --}}
                <flux:field>
                    <flux:label>募集期限</flux:label>
                    <flux:input wire:model="expires_at" type="date" />
                    <flux:description>
                        指定しない場合は期限なしとなります
                    </flux:description>
                    <flux:error name="expires_at" />
                </flux:field>

                {{-- 送信ボタン --}}
                <div class="flex justify-end gap-2">
                    <flux:button :href="route('jobs.index')" wire:navigate variant="ghost">
                        キャンセル
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        登録する
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
