<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount, computed, rules, layout, title};

// レイアウトとタイトルを指定
layout('components.layouts.app');
title('企業プロフィール編集');

// フォーム状態
state([
    'location_id' => null,
    'address' => '',
    'representative' => '',
    'phone_number' => '',
    'selectedPrefecture' => null,
]);

// バリデーションルール
rules([
    'location_id' => 'required|exists:locations,id',
    'address' => 'required|string|max:200',
    'representative' => 'required|string|max:50',
    'phone_number' => 'required|string|max:30',
]);

// カスタムバリデーションメッセージ
$messages = [
    'location_id.required' => '所在地を選択してください。',
    'location_id.exists' => '選択された所在地は存在しません。',
    'address.required' => '所在地住所を入力してください。',
    'address.max' => '所在地住所は200文字以内で入力してください。',
    'representative.required' => '担当者名を入力してください。',
    'representative.max' => '担当者名は50文字以内で入力してください。',
    'phone_number.required' => '担当者連絡先を入力してください。',
    'phone_number.max' => '担当者連絡先は30文字以内で入力してください。',
];

// 初期化処理
mount(function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $profile = CompanyProfile::query()->with('location')->where('user_id', $user->id)->firstOrFail();

    // 既存データをフォームにセット
    $this->location_id = $profile->location_id;
    $this->address = $profile->address;
    $this->representative = $profile->representative;
    $this->phone_number = $profile->phone_number;

    // 選択された市区町村から都道府県を取得
    $selectedLocation = Location::query()->find($this->location_id);
    if ($selectedLocation) {
        $prefecture = Location::query()->where('prefecture', $selectedLocation->prefecture)->whereNull('city')->first();
        if ($prefecture) {
            $this->selectedPrefecture = $prefecture->id;
        }
    }
});

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

// 都道府県変更時の処理
$updatedSelectedPrefecture = function () {
    // 都道府県を変更したら市区町村の選択をリセット
    $this->location_id = null;
};

// 更新処理
$update = function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    // バリデーション
    $this->validate();

    // プロフィール取得
    $profile = CompanyProfile::query()->where('user_id', $user->id)->firstOrFail();

    // 更新
    $profile->update([
        'location_id' => $this->location_id,
        'address' => $this->address,
        'representative' => $this->representative,
        'phone_number' => $this->phone_number,
    ]);

    // 詳細画面にリダイレクト
    return $this->redirect(route('company.profile'), navigate: true);
};

?>

<div>
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between">
            <flux:heading size="xl">企業プロフィール編集</flux:heading>
            <flux:button variant="ghost" :href="route('company.profile')" wire:navigate>
                戻る
            </flux:button>
        </div>

        {{-- フォーム --}}
        <form wire:submit="update" class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="space-y-6">
                    {{-- 所在地（都道府県） --}}
                    <flux:field>
                        <flux:label>所在地（都道府県）</flux:label>
                        <flux:select wire:model.live="selectedPrefecture" placeholder="都道府県を選択...">
                            @foreach ($this->prefectures as $prefecture)
                                <option value="{{ $prefecture->id }}">{{ $prefecture->prefecture }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="selectedPrefecture" />
                    </flux:field>

                    {{-- 所在地（市区町村） --}}
                    <flux:field>
                        <flux:label>所在地（市区町村）</flux:label>
                        <flux:select wire:model="location_id" placeholder="市区町村を選択..."
                            :disabled="$selectedPrefecture === null">
                            @foreach ($this->cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="location_id" />
                    </flux:field>

                    {{-- 所在地住所 --}}
                    <flux:field>
                        <flux:label>所在地住所</flux:label>
                        <flux:input wire:model="address" type="text" placeholder="例：中央区北1条西5丁目" />
                        <flux:error name="address" />
                    </flux:field>

                    {{-- 担当者名 --}}
                    <flux:field>
                        <flux:label>担当者名</flux:label>
                        <flux:input wire:model="representative" type="text" placeholder="例：山田 太郎" />
                        <flux:error name="representative" />
                    </flux:field>

                    {{-- 担当者連絡先 --}}
                    <flux:field>
                        <flux:label>担当者連絡先</flux:label>
                        <flux:input wire:model="phone_number" type="text" placeholder="例：011-123-4567" />
                        <flux:error name="phone_number" />
                    </flux:field>
                </div>
            </div>

            {{-- アクションボタン --}}
            <div class="flex items-center justify-end gap-4">
                <flux:button type="button" variant="ghost" :href="route('company.profile')" wire:navigate>
                    キャンセル
                </flux:button>
                <flux:button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="update">更新する</span>
                    <span wire:loading wire:target="update">更新中...</span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
