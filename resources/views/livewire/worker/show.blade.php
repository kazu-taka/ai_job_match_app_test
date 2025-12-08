<?php

declare(strict_types=1);

use App\Models\WorkerProfile;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount, layout, title};

// レイアウトとタイトルを指定
layout('components.layouts.app');
title('ワーカープロフィール');

// 表示用のデータを状態として保持
state([
    'workerName' => '',
    'gender' => '',
    'birthdate' => '',
    'age' => '',
    'skills' => '',
    'experiences' => '',
    'desiredJobs' => '',
    'desiredLocation' => '',
    'createdAt' => '',
    'updatedAt' => '',
]);

mount(function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $profile = WorkerProfile::query()
        ->with(['user', 'desiredLocation'])
        ->where('user_id', $user->id)
        ->firstOrFail();

    // データを文字列として状態に保存
    $this->workerName = $profile->user->name;

    // 性別の日本語表示
    $this->gender = match ($profile->gender) {
        'male' => '男性',
        'female' => '女性',
        'other' => 'その他',
        default => '未設定',
    };

    // 生年月日と年齢の計算
    $this->birthdate = $profile->birthdate->format('Y年m月d日');
    $this->age = (string) $profile->birthdate->age;

    // 任意項目（NULLの場合は「未設定」と表示）
    $this->skills = $profile->skills ?? '未設定';
    $this->experiences = $profile->experiences ?? '未設定';
    $this->desiredJobs = $profile->desired_jobs ?? '未設定';

    // 希望勤務地の表示（都道府県 市区町村）
    if ($profile->desiredLocation) {
        $prefecture = $profile->desiredLocation->prefecture;
        $city = $profile->desiredLocation->city;
        $this->desiredLocation = $city ? "{$prefecture} {$city}" : $prefecture;
    } else {
        $this->desiredLocation = '未設定';
    }

    $this->createdAt = $profile->created_at->format('Y年m月d日 H:i');
    $this->updatedAt = $profile->updated_at->format('Y年m月d日 H:i');
});

?>

<div>
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between">
            <flux:heading size="xl">ワーカープロフィール</flux:heading>
            <flux:button :href="route('worker.edit')" wire:navigate>
                編集
            </flux:button>
        </div>

        {{-- プロフィール詳細 --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="space-y-6">
                {{-- 氏名 --}}
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">氏名</flux:text>
                    <flux:text class="mt-1 text-lg">{{ $workerName }}</flux:text>
                </div>

                {{-- 性別 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">性別</flux:text>
                    <flux:text class="mt-1">{{ $gender }}</flux:text>
                </div>

                {{-- 生年月日・年齢 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">生年月日</flux:text>
                    <flux:text class="mt-1">{{ $birthdate }}（{{ $age }}歳）</flux:text>
                </div>

                {{-- スキル --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">スキル</flux:text>
                    <flux:text class="mt-1 whitespace-pre-wrap">{{ $skills }}</flux:text>
                </div>

                {{-- 経験 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">経験</flux:text>
                    <flux:text class="mt-1 whitespace-pre-wrap">{{ $experiences }}</flux:text>
                </div>

                {{-- 希望職種 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">希望職種</flux:text>
                    <flux:text class="mt-1">{{ $desiredJobs }}</flux:text>
                </div>

                {{-- 希望勤務地 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">希望勤務地</flux:text>
                    <flux:text class="mt-1">{{ $desiredLocation }}</flux:text>
                </div>

                {{-- 登録日時 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">登録日時</flux:text>
                    <flux:text class="mt-1">{{ $createdAt }}</flux:text>
                </div>

                {{-- 更新日時 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">更新日時</flux:text>
                    <flux:text class="mt-1">{{ $updatedAt }}</flux:text>
                </div>
            </div>
        </div>
    </div>
</div>
