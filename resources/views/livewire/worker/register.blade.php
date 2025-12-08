<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

// レイアウトとタイトルを指定（未認証ユーザー向け）
layout('components.layouts.auth');
title('ワーカー登録');

// フォーム状態
state([
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'gender' => '',
    'birthYear' => null,
    'birthMonth' => null,
    'birthDay' => null,
    'skills' => '',
    'experiences' => '',
    'desired_jobs' => '',
    'desired_location_id' => null,
    'selectedPrefecture' => null,
]);

// バリデーションルール
rules([
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:users',
    'password' => 'required|string|min:8|confirmed',
    'gender' => 'required|in:male,female,other',
    'birthYear' => 'required|integer',
    'birthMonth' => 'required|integer|min:1|max:12',
    'birthDay' => 'required|integer|min:1|max:31',
    'skills' => 'nullable|string|max:200',
    'experiences' => 'nullable|string|max:200',
    'desired_jobs' => 'nullable|string|max:200',
    'desired_location_id' => 'nullable|exists:locations,id',
]);

// カスタムバリデーションメッセージ
$messages = [
    'name.required' => '氏名を入力してください。',
    'name.max' => '氏名は255文字以内で入力してください。',
    'email.required' => 'メールアドレスを入力してください。',
    'email.email' => '有効なメールアドレスを入力してください。',
    'email.max' => 'メールアドレスは255文字以内で入力してください。',
    'email.unique' => 'このメールアドレスは既に使用されています。',
    'password.required' => 'パスワードを入力してください。',
    'password.min' => 'パスワードは8文字以上で入力してください。',
    'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
    'gender.required' => '性別を選択してください。',
    'gender.in' => '性別は「男性」「女性」「その他」から選択してください。',
    'birthYear.required' => '生年月日（年）を選択してください。',
    'birthYear.integer' => '生年月日（年）は数値で入力してください。',
    'birthMonth.required' => '生年月日（月）を選択してください。',
    'birthMonth.integer' => '生年月日（月）は数値で入力してください。',
    'birthMonth.min' => '生年月日（月）は1〜12の範囲で入力してください。',
    'birthMonth.max' => '生年月日（月）は1〜12の範囲で入力してください。',
    'birthDay.required' => '生年月日（日）を選択してください。',
    'birthDay.integer' => '生年月日（日）は数値で入力してください。',
    'birthDay.min' => '生年月日（日）は1〜31の範囲で入力してください。',
    'birthDay.max' => '生年月日（日）は1〜31の範囲で入力してください。',
    'skills.max' => 'スキルは200文字以内で入力してください。',
    'experiences.max' => '経験は200文字以内で入力してください。',
    'desired_jobs.max' => '希望職種は200文字以内で入力してください。',
    'desired_location_id.exists' => '選択された希望勤務地は存在しません。',
];

// 年リストを取得（現在年 - 18歳 から 現在年 - 80歳 まで）
$years = computed(function () {
    $currentYear = (int) date('Y');
    $startYear = $currentYear - 18;
    $endYear = $currentYear - 80;
    $years = [];
    for ($year = $startYear; $year >= $endYear; $year--) {
        $years[] = $year;
    }

    return $years;
});

// 月リストを取得
$months = computed(function () {
    return range(1, 12);
});

// 日リストを取得（選択された年月に応じて動的に生成）
$days = computed(function () {
    if ($this->birthYear === null || $this->birthMonth === null) {
        return range(1, 31);
    }

    // 選択された年月の最終日を取得
    $maxDay = (int) date('t', mktime(0, 0, 0, (int) $this->birthMonth, 1, (int) $this->birthYear));

    return range(1, $maxDay);
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

// 年または月変更時の処理（日のバリデーション）
$updatedBirthYear = function () {
    if ($this->birthYear !== null && $this->birthMonth !== null) {
        $maxDay = (int) date('t', mktime(0, 0, 0, (int) $this->birthMonth, 1, (int) $this->birthYear));
        if ($this->birthDay !== null && (int) $this->birthDay > $maxDay) {
            $this->birthDay = null;
        }
    }
};

$updatedBirthMonth = function () {
    if ($this->birthYear !== null && $this->birthMonth !== null) {
        $maxDay = (int) date('t', mktime(0, 0, 0, (int) $this->birthMonth, 1, (int) $this->birthYear));
        if ($this->birthDay !== null && (int) $this->birthDay > $maxDay) {
            $this->birthDay = null;
        }
    }
};

// 都道府県変更時の処理
$updatedSelectedPrefecture = function () {
    // 都道府県を変更したら市区町村の選択をリセット
    $this->desired_location_id = null;
};

// 登録処理
$register = function () {
    // バリデーション
    $this->validate();

    // 生年月日を日付文字列に変換
    $birthdate = sprintf('%04d-%02d-%02d', $this->birthYear, $this->birthMonth, $this->birthDay);

    // トランザクション内でユーザーとプロフィールを登録
    DB::transaction(function () use ($birthdate) {
        // ユーザー登録
        $user = User::query()->create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'worker',
            'email_verified_at' => now(),
        ]);

        // ワーカープロフィール登録
        WorkerProfile::query()->create([
            'user_id' => $user->id,
            'gender' => $this->gender,
            'birthdate' => $birthdate,
            'skills' => $this->skills ?: null,
            'experiences' => $this->experiences ?: null,
            'desired_jobs' => $this->desired_jobs ?: null,
            'desired_location_id' => $this->desired_location_id,
        ]);

        // 自動ログイン
        Auth::login($user);
    });

    // ワーカー詳細画面にリダイレクト
    return $this->redirect(route('worker.profile'), navigate: true);
};

?>

<div>
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between">
            <flux:heading size="xl">ワーカー登録</flux:heading>
            <flux:button variant="ghost" :href="route('home')" wire:navigate>
                トップに戻る
            </flux:button>
        </div>

        {{-- 説明 --}}
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950">
            <flux:text>
                ワーカーアカウントを登録します。登録後、すぐに求人への応募が可能になります。
            </flux:text>
        </div>

        {{-- フォーム --}}
        <form wire:submit="register" class="space-y-6">
            {{-- ユーザー情報 --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <flux:heading size="lg" class="mb-4">ユーザー情報</flux:heading>
                <div class="space-y-6">
                    {{-- 氏名 --}}
                    <flux:field>
                        <flux:label>氏名</flux:label>
                        <flux:input wire:model="name" type="text" placeholder="例：佐藤 太郎" />
                        <flux:error name="name" />
                    </flux:field>

                    {{-- メールアドレス --}}
                    <flux:field>
                        <flux:label>メールアドレス</flux:label>
                        <flux:input wire:model="email" type="email" placeholder="例：sato.taro@gmail.com" />
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

            {{-- ワーカープロフィール --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <flux:heading size="lg" class="mb-4">ワーカープロフィール</flux:heading>
                <div class="space-y-6">
                    {{-- 性別 --}}
                    <flux:field>
                        <flux:label>性別</flux:label>
                        <div class="flex gap-6">
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model="gender" value="male"
                                    class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span>男性</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model="gender" value="female"
                                    class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span>女性</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model="gender" value="other"
                                    class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span>その他</span>
                            </label>
                        </div>
                        <flux:error name="gender" />
                    </flux:field>

                    {{-- 生年月日 --}}
                    <flux:field>
                        <flux:label>生年月日</flux:label>
                        <div class="flex gap-4">
                            <select wire:model.live="birthYear"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                <option value="">年</option>
                                @foreach ($this->years as $year)
                                    <option value="{{ $year }}">{{ $year }}年</option>
                                @endforeach
                            </select>

                            <select wire:model.live="birthMonth"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                <option value="">月</option>
                                @foreach ($this->months as $month)
                                    <option value="{{ $month }}">{{ $month }}月</option>
                                @endforeach
                            </select>

                            <select wire:model="birthDay"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                <option value="">日</option>
                                @foreach ($this->days as $day)
                                    <option value="{{ $day }}">{{ $day }}日</option>
                                @endforeach
                            </select>
                        </div>
                        <flux:error name="birthYear" />
                        <flux:error name="birthMonth" />
                        <flux:error name="birthDay" />
                    </flux:field>

                    {{-- スキル --}}
                    <flux:field>
                        <flux:label>スキル（任意）</flux:label>
                        <flux:textarea wire:model="skills" rows="3"
                            placeholder="例：英語（TOEIC750点）、Excel、プログラミング（Laravel）">{{ $skills }}</flux:textarea>
                        <flux:error name="skills" />
                    </flux:field>

                    {{-- 経験 --}}
                    <flux:field>
                        <flux:label>経験（任意）</flux:label>
                        <flux:textarea wire:model="experiences" rows="3" placeholder="例：飲食店勤務 5年、ホテルフロント 3年">
                            {{ $experiences }}</flux:textarea>
                        <flux:error name="experiences" />
                    </flux:field>

                    {{-- 希望職種 --}}
                    <flux:field>
                        <flux:label>希望職種（任意）</flux:label>
                        <flux:input wire:model="desired_jobs" type="text" placeholder="例：接客・サービス" />
                        <flux:error name="desired_jobs" />
                    </flux:field>

                    {{-- 希望勤務地（都道府県） --}}
                    <flux:field>
                        <flux:label>希望勤務地（都道府県）（任意）</flux:label>
                        <flux:select wire:model.live="selectedPrefecture" placeholder="選択しない">
                            @foreach ($this->prefectures as $prefecture)
                                <option value="{{ $prefecture->id }}">{{ $prefecture->prefecture }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="selectedPrefecture" />
                    </flux:field>

                    {{-- 希望勤務地（市区町村） --}}
                    <flux:field>
                        <flux:label>希望勤務地（市区町村）（任意）</flux:label>
                        <flux:select wire:model="desired_location_id" placeholder="選択しない"
                            :disabled="$selectedPrefecture === null">
                            @foreach ($this->cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="desired_location_id" />
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
