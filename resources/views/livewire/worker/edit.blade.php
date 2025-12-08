<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\WorkerProfile;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount, computed, rules, layout, title};

// レイアウトとタイトルを指定
layout('components.layouts.app');
title('ワーカープロフィール編集');

// フォーム状態
state([
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
    'gender' => 'required|in:male,female,other',
    'birthYear' => 'required|integer|min:' . (now()->year - 80) . '|max:' . (now()->year - 18),
    'birthMonth' => 'required|integer|min:1|max:12',
    'birthDay' => 'required|integer|min:1|max:31',
    'skills' => 'nullable|string|max:200',
    'experiences' => 'nullable|string|max:200',
    'desired_jobs' => 'nullable|string|max:200',
    'desired_location_id' => 'nullable|exists:locations,id',
]);

// カスタムバリデーションメッセージ
$messages = [
    'gender.required' => '性別を選択してください。',
    'gender.in' => '性別は男性、女性、その他のいずれかを選択してください。',
    'birthYear.required' => '生年月日（年）を選択してください。',
    'birthYear.integer' => '生年月日（年）は整数で入力してください。',
    'birthYear.min' => '生年月日（年）は' . (now()->year - 80) . '年以降を選択してください。',
    'birthYear.max' => '生年月日（年）は' . (now()->year - 18) . '年以前を選択してください。',
    'birthMonth.required' => '生年月日（月）を選択してください。',
    'birthMonth.integer' => '生年月日（月）は整数で入力してください。',
    'birthMonth.min' => '生年月日（月）は1月から12月の範囲で選択してください。',
    'birthMonth.max' => '生年月日（月）は1月から12月の範囲で選択してください。',
    'birthDay.required' => '生年月日（日）を選択してください。',
    'birthDay.integer' => '生年月日（日）は整数で入力してください。',
    'birthDay.min' => '生年月日（日）は1日以降を選択してください。',
    'birthDay.max' => '生年月日（日）は31日以前を選択してください。',
    'skills.max' => 'スキルは200文字以内で入力してください。',
    'experiences.max' => '経験は200文字以内で入力してください。',
    'desired_jobs.max' => '希望職種は200文字以内で入力してください。',
    'desired_location_id.exists' => '選択された希望勤務地は存在しません。',
];

// 初期化処理
mount(function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $profile = WorkerProfile::query()->with('desiredLocation')->where('user_id', $user->id)->firstOrFail();

    // 既存データをフォームにセット
    $this->gender = $profile->gender;

    // 生年月日を年月日に分割
    $this->birthYear = (int) $profile->birthdate->format('Y');
    $this->birthMonth = (int) $profile->birthdate->format('m');
    $this->birthDay = (int) $profile->birthdate->format('d');

    $this->skills = $profile->skills ?? '';
    $this->experiences = $profile->experiences ?? '';
    $this->desired_jobs = $profile->desired_jobs ?? '';
    $this->desired_location_id = $profile->desired_location_id;

    // 選択された市区町村から都道府県を取得
    if ($this->desired_location_id) {
        $selectedLocation = Location::query()->find($this->desired_location_id);
        if ($selectedLocation) {
            $prefecture = Location::query()->where('prefecture', $selectedLocation->prefecture)->whereNull('city')->first();
            if ($prefecture) {
                $this->selectedPrefecture = $prefecture->id;
            }
        }
    }
});

// 年のリストを生成
$years = computed(function () {
    $currentYear = now()->year;
    $years = [];
    for ($year = $currentYear - 18; $year >= $currentYear - 80; $year--) {
        $years[] = $year;
    }

    return $years;
});

// 月のリストを生成
$months = computed(function () {
    return range(1, 12);
});

// 年または月が変更されたときの処理
$updatedBirthYear = function () {
    // 選択中の日が新しい日数を超える場合、日選択をリセット
    if ($this->birthYear !== null && $this->birthMonth !== null) {
        $maxDay = (int) date('t', mktime(0, 0, 0, (int) $this->birthMonth, 1, (int) $this->birthYear));
        if ($this->birthDay !== null && $this->birthDay > $maxDay) {
            $this->birthDay = null;
        }
    }
};

$updatedBirthMonth = function () {
    // 選択中の日が新しい日数を超える場合、日選択をリセット
    if ($this->birthYear !== null && $this->birthMonth !== null) {
        $maxDay = (int) date('t', mktime(0, 0, 0, (int) $this->birthMonth, 1, (int) $this->birthYear));
        if ($this->birthDay !== null && $this->birthDay > $maxDay) {
            $this->birthDay = null;
        }
    }
};

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
    $this->desired_location_id = null;

    // 都道府県を「選択しない」にした場合
    if ($this->selectedPrefecture === null) {
        $this->desired_location_id = null;
    }
};

// 更新処理
$update = function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    // バリデーション
    $validated = $this->validate();

    // 生年月日の妥当性チェック（wire:modelで取得した値は文字列なので整数にキャスト）
    if (!checkdate((int) $this->birthMonth, (int) $this->birthDay, (int) $this->birthYear)) {
        $this->addError('birthDay', '無効な日付です。');

        return;
    }

    // プロフィール取得
    $profile = WorkerProfile::query()->where('user_id', $user->id)->firstOrFail();

    // 生年月日を結合
    $birthdate = sprintf('%04d-%02d-%02d', (int) $this->birthYear, (int) $this->birthMonth, (int) $this->birthDay);

    // 更新
    $profile->update([
        'gender' => $this->gender,
        'birthdate' => $birthdate,
        'skills' => $this->skills ?: null,
        'experiences' => $this->experiences ?: null,
        'desired_jobs' => $this->desired_jobs ?: null,
        'desired_location_id' => $this->desired_location_id,
    ]);

    // 詳細画面にリダイレクト
    return $this->redirect(route('worker.profile'), navigate: true);
};

?>

<div>
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between">
            <flux:heading size="xl">ワーカープロフィール編集</flux:heading>
            <flux:button variant="ghost" :href="route('worker.profile')" wire:navigate>
                戻る
            </flux:button>
        </div>

        {{-- フォーム --}}
        <form wire:submit="update" class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
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
                            <div class="flex-1">
                                <select wire:model.live="birthYear"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                    <option value="">年</option>
                                    @foreach ($this->years as $year)
                                        <option value="{{ $year }}">{{ $year }}年</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1">
                                <select wire:model.live="birthMonth"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                    <option value="">月</option>
                                    @foreach ($this->months as $month)
                                        <option value="{{ $month }}">{{ $month }}月</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1">
                                <select wire:model="birthDay"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                    <option value="">日</option>
                                    @php
                                        $maxDay = 31;
                                        if ($birthYear && $birthMonth) {
                                            $maxDay = (int) date(
                                                't',
                                                mktime(0, 0, 0, (int) $birthMonth, 1, (int) $birthYear),
                                            );
                                        }
                                    @endphp
                                    @for ($day = 1; $day <= $maxDay; $day++)
                                        <option value="{{ $day }}">{{ $day }}日</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <flux:error name="birthYear" />
                        <flux:error name="birthMonth" />
                        <flux:error name="birthDay" />
                    </flux:field>

                    {{-- スキル --}}
                    <flux:field>
                        <flux:label>スキル（任意）</flux:label>
                        <flux:textarea wire:model="skills" rows="3" placeholder="例：接客、英語（TOEIC750点）、Excel">
                            {{ $skills }}</flux:textarea>
                        <flux:error name="skills" />
                        <flux:text class="text-sm text-gray-500 dark:text-gray-400">200文字以内</flux:text>
                    </flux:field>

                    {{-- 経験 --}}
                    <flux:field>
                        <flux:label>経験（任意）</flux:label>
                        <flux:textarea wire:model="experiences" rows="3" placeholder="例：飲食店勤務 5年、ホテルフロント 3年">
                            {{ $experiences }}</flux:textarea>
                        <flux:error name="experiences" />
                        <flux:text class="text-sm text-gray-500 dark:text-gray-400">200文字以内</flux:text>
                    </flux:field>

                    {{-- 希望職種 --}}
                    <flux:field>
                        <flux:label>希望職種（任意）</flux:label>
                        <flux:input wire:model="desired_jobs" type="text" placeholder="例：接客・サービス" />
                        <flux:error name="desired_jobs" />
                        <flux:text class="text-sm text-gray-500 dark:text-gray-400">200文字以内</flux:text>
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
                <flux:button type="button" variant="ghost" :href="route('worker.profile')" wire:navigate>
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
