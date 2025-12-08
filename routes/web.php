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
});

// ワーカーユーザー専用のルート
Route::middleware(['auth', 'role:worker'])->group(function () {
    Volt::route('worker/profile', 'worker.show')->name('worker.profile');
    Volt::route('worker/edit', 'worker.edit')->name('worker.edit');
});
