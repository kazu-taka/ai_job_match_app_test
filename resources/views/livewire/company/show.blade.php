<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount, layout, title};

// レイアウトとタイトルを指定
layout('components.layouts.app');
title('企業プロフィール');

// 表示用のデータを状態として保持
state([
    'companyName' => '',
    'prefecture' => '',
    'city' => '',
    'address' => '',
    'representative' => '',
    'phoneNumber' => '',
    'createdAt' => '',
    'updatedAt' => '',
]);

mount(function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $profile = CompanyProfile::query()
        ->with(['user', 'location'])
        ->where('user_id', $user->id)
        ->firstOrFail();

    // データを文字列として状態に保存
    $this->companyName = $profile->user->name;
    $this->prefecture = $profile->location->prefecture;
    $this->city = $profile->location->city ?? '';
    $this->address = $profile->address;
    $this->representative = $profile->representative;
    $this->phoneNumber = $profile->phone_number;
    $this->createdAt = $profile->created_at->format('Y年m月d日 H:i');
    $this->updatedAt = $profile->updated_at->format('Y年m月d日 H:i');
});

?>

<div>
    @php
        $fullLocation = $city ? "{$prefecture} {$city}" : $prefecture;
    @endphp

    <div class="mx-auto max-w-4xl space-y-6 p-6">
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between">
            <flux:heading size="xl">企業プロフィール</flux:heading>
            <flux:button disabled>
                編集（準備中）
            </flux:button>
        </div>

        {{-- プロフィール詳細 --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="space-y-6">
                {{-- 企業名 --}}
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">企業名</flux:text>
                    <flux:text class="mt-1 text-lg">{{ $companyName }}</flux:text>
                </div>

                {{-- 所在地 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">所在地</flux:text>
                    <flux:text class="mt-1">{{ $fullLocation }}</flux:text>
                </div>

                {{-- 所在地住所 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">所在地住所</flux:text>
                    <flux:text class="mt-1">{{ $address }}</flux:text>
                </div>

                {{-- 担当者名 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">担当者名</flux:text>
                    <flux:text class="mt-1">{{ $representative }}</flux:text>
                </div>

                {{-- 担当者連絡先 --}}
                <flux:separator />
                <div>
                    <flux:text class="font-semibold text-gray-600 dark:text-gray-400">担当者連絡先</flux:text>
                    <flux:text class="mt-1">{{ $phoneNumber }}</flux:text>
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
