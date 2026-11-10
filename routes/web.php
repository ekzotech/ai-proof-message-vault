<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordRecoveryController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\CapsuleDeletionController;
use App\Http\Controllers\CapsulePreviewController;
use App\Http\Controllers\CapsulePublicationController;
use App\Http\Controllers\CapsuleReaderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Models\Capsule;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/c/{token}', CapsuleReaderController::class)->where('token', '[a-f0-9]{48}')->name('capsules.read');

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
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::view('/capsules/create', 'capsules.create')->can('create', Capsule::class)->name('capsules.create');
    Route::get('/capsules/{capsule}/edit', fn (Capsule $capsule) => view('capsules.edit', compact('capsule')))
        ->can('update', 'capsule')->name('capsules.edit');
    Route::get('/capsules/{capsule}/preview', CapsulePreviewController::class)
        ->can('update', 'capsule')->name('capsules.preview');
    Route::post('/capsules/{capsule}/publish', [CapsulePublicationController::class, 'store'])
        ->middleware('verified')->can('publish', 'capsule')->name('capsules.publish');
    Route::get('/capsules/{capsule}/publication', [CapsulePublicationController::class, 'show'])
        ->can('view', 'capsule')->name('capsules.publication');
    Route::patch('/capsules/{capsule}/revoke', [CapsulePublicationController::class, 'revoke'])
        ->can('view', 'capsule')->name('capsules.revoke');
    Route::get('/capsules/{capsule}/delete', [CapsuleDeletionController::class, 'confirm'])
        ->can('delete', 'capsule')->name('capsules.delete');
    Route::delete('/capsules/{capsule}', [CapsuleDeletionController::class, 'destroy'])
        ->can('delete', 'capsule')->name('capsules.destroy');
    Route::view('/profile', 'profile')->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::view('/verify-email', 'auth.verify-email')->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed:relative')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:3,1')->name('verification.send');
});
