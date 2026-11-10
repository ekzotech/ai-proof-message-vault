<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordRecoveryController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', RegisterController::class);
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [SessionController::class, 'store']);
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', [PasswordRecoveryController::class, 'send'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::view('/reset-password/{token}', 'auth.reset-password')->name('password.reset');
    Route::post('/reset-password', [PasswordRecoveryController::class, 'reset'])
        ->middleware('throttle:10,1')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/profile', 'profile')->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::view('/verify-email', 'auth.verify-email')->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed:relative')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:3,1')->name('verification.send');
});
