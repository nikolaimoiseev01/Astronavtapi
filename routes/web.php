<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Pages\Account\SettingsPage;
use App\Livewire\Pages\Auth\ConfirmPasswordPage;
use App\Livewire\Pages\Auth\ForgotPasswordPage;
use App\Livewire\Pages\Auth\LoginPage;
use App\Livewire\Pages\Auth\RegisterPage;
use App\Livewire\Pages\Auth\ResetPasswordPage;
use App\Livewire\Pages\Auth\VerifyEmailPage;
use App\Livewire\Pages\Portal\IndexPage;
use Illuminate\Support\Facades\Route;


//region Auth Routes
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('guest')->group(function () {
    Route::get('register', RegisterPage::class)
        ->name('auth.register');

    Route::get('login', LoginPage::class)
        ->name('auth.login');

    Route::get('forgot-password', ForgotPasswordPage::class)
        ->name('auth.password.request');

    Route::get('reset-password/{token}', ResetPasswordPage::class)
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', VerifyEmailPage::class)
        ->name('auth.verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('auth.verification.verify');

    Route::get('confirm-password', ConfirmPasswordPage::class)
        ->name('auth.password.confirm');
});
//endregion Auth


Route::get('/', IndexPage::class)->name('portal.index');

Route::middleware('auth')->prefix('account')->group(function () {
    Route::get('settings', SettingsPage::class)->middleware(['auth', 'verified'])->name('account.settings');
});

