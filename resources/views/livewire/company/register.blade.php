<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use function Livewire\Volt\{state, computed, rules, layout, title};

// レイアウトとタイトルを指定（未認証ユーザー向け）
layout('components.layouts.auth');
title('企業登録');

// フォーム状態
state([
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'location_id' => null,
    'address' => '',
    'representative' => '',
    'phone_number' => '',
    'selectedPrefecture' => null,
]);

// バリデーションルール
rules([
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:users',
    'password' => 'required|string|min:8|confirmed',
    'location_id' => 'required|exists:locations,id',
    'address' => 'required|string|max:200',
    'representative' => 'required|string|max:50',
    'phone_number' => 'required|string|max:30',
]);

// カスタムバリデーションメッセージ
$messages = [
    'name.required' => '企業名を入力してください。',
    'name.max' => '企業名は255文字以内で入力してください。',
    'email.required' => 'メールアドレスを入力してください。',
    'email.email' => '有効なメールアドレスを入力してください。',
    'email.max' => 'メールアドレスは255文字以内で入力してください。',
    'email.unique' => 'このメールアドレスは既に使用されています。',
    'password.required' => 'パスワードを入力してください。',
    'password.min' => 'パスワードは8文字以上で入力してください。',
    'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
    'location_id.required' => '所在地を選択してください。',
    'location_id.exists' => '選択された所在地は存在しません。',
    'address.required' => '所在地住所を入力してください。',
    'address.max' => '所在地住所は200文字以内で入力してください。',
    'representative.required' => '担当者名を入力してください。',
    'representative.max' => '担当者名は50文字以内で入力してください。',
    'phone_number.required' => '担当者連絡先を入力してください。',
    'phone_number.max' => '担当者連絡先は30文字以内で入力してください。',
];

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

// 登録処理
$register = function () {
    // バリデーション
    $this->validate();

    // トランザクション内でユーザーとプロフィールを登録
    DB::transaction(function () {
        // ユーザー登録
        $user = User::query()->create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'company',
            'email_verified_at' => now(),
        ]);

        // 企業プロフィール登録
        CompanyProfile::query()->create([
            'user_id' => $user->id,
            'location_id' => $this->location_id,
            'address' => $this->address,
            'representative' => $this->representative,
            'phone_number' => $this->phone_number,
        ]);

        // 自動ログイン
        Auth::login($user);
    });

    // 企業詳細画面にリダイレクト
    return $this->redirect(route('company.profile'), navigate: true);
};

?>

<div>
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between">
            <flux:heading size="xl">企業登録</flux:heading>
            <flux:button variant="ghost" :href="route('home')" wire:navigate>
                トップに戻る
            </flux:button>
        </div>

        {{-- 説明 --}}
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950">
            <flux:text>
                企業アカウントを登録します。登録後、すぐに求人の掲載が可能になります。
            </flux:text>
        </div>

        {{-- フォーム --}}
        <form wire:submit="register" class="space-y-6">
            {{-- ユーザー情報 --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <flux:heading size="lg" class="mb-4">ユーザー情報</flux:heading>
                <div class="space-y-6">
                    {{-- 企業名 --}}
                    <flux:field>
                        <flux:label>企業名</flux:label>
                        <flux:input wire:model="name" type="text" placeholder="例：株式会社北海水産" />
                        <flux:error name="name" />
                    </flux:field>

                    {{-- メールアドレス --}}
                    <flux:field>
                        <flux:label>メールアドレス</flux:label>
                        <flux:input wire:model="email" type="email" placeholder="例：info@company.co.jp" />
                        <flux:error name="email" />
                    </flux:field>

                    {{-- パスワード --}}
                    <flux:field>
                        <flux:label>パスワード</flux:label>
                        <flux:input wire:model="password" type="password" placeholder="8文字以上" />
                        <flux:error name="password" />
                    </flux:field>

                    {{-- パスワード確認 --}}
                    <flux:field>
                        <flux:label>パスワード（確認）</flux:label>
                        <flux:input wire:model="password_confirmation" type="password" placeholder="もう一度入力してください" />
                        <flux:error name="password_confirmation" />
                    </flux:field>
                </div>
            </div>

            {{-- 企業プロフィール --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <flux:heading size="lg" class="mb-4">企業プロフィール</flux:heading>
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
                <flux:button type="button" variant="ghost" :href="route('home')" wire:navigate>
                    キャンセル
                </flux:button>
                <flux:button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="register">登録する</span>
                    <span wire:loading wire:target="register">登録中...</span>
                </flux:button>
            </div>
        </form>

        {{-- 既存アカウントへのリンク --}}
        <div class="text-center">
            <flux:text>
                すでにアカウントをお持ちの方は
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline dark:text-blue-400" wire:navigate>
                    こちらからログイン
                </a>
            </flux:text>
        </div>
    </div>
</div>

