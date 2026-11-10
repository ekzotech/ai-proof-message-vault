<?php

use App\Http\Controllers\Auth\PasswordRecoveryController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
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
});

Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');
