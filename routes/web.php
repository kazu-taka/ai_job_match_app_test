<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// 企業登録（未認証ユーザー向け）
Volt::route('company/register', 'company.register')->name('company.register');

// ワーカー登録（未認証ユーザー向け）
Volt::route('worker/register', 'worker.register')->name('worker.register');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// 企業ユーザー専用のルート
Route::middleware(['auth', 'role:company'])->group(function () {
    Volt::route('company/profile', 'company.show')->name('company.profile');
    Volt::route('company/edit', 'company.edit')->name('company.edit');
    Volt::route('applications/received', 'applications.received')->name('applications.received');
});

// ワーカーユーザー専用のルート
Route::middleware(['auth', 'role:worker'])->group(function () {
    Volt::route('worker/profile', 'worker.show')->name('worker.profile');
    Volt::route('worker/edit', 'worker.edit')->name('worker.edit');
});

// 求人投稿管理（認証必須）
Route::middleware(['auth'])->group(function () {
    Volt::route('jobs', 'jobs.index')->name('jobs.index');
});

// 求人投稿管理（企業ユーザー専用）
Route::middleware(['auth', 'role:company'])->group(function () {
    Volt::route('jobs/create', 'jobs.create')->name('jobs.create');
    Volt::route('jobs/{jobPost}/edit', 'jobs.edit')->name('jobs.edit');
});

// 求人応募管理（ワーカーユーザー専用）
Route::middleware(['auth', 'role:worker'])->group(function () {
    Volt::route('jobs/{jobPost}/apply', 'jobs.apply')->name('jobs.apply');
    Volt::route('applications', 'applications.index')->name('applications.index');
});

// 応募管理（認証必須 - workerとcompany共通）
Route::middleware(['auth'])->group(function () {
    Volt::route('applications/{jobApplication}', 'applications.show')->name('applications.show');
});

// 求人投稿管理（認証必須） - 動的ルートは最後に配置
Route::middleware(['auth'])->group(function () {
    Volt::route('jobs/{jobPost}', 'jobs.show')->name('jobs.show');
});
