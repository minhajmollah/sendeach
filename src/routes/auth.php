<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\GoogleAuthenticatedController;
use App\Http\Middleware\ValidateCaptcha;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest','maintanance'])->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register')->middleware('registration.allow');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('registration.allow');

    Route::post('registration/verify', [RegisteredUserController::class, 'verify'])->name('registration.verify')->middleware('registration.allow');

    Route::get('registration/verify/code', [RegisteredUserController::class, 'verifyCode'])->name('registration.verify.code')->middleware('registration.allow');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'send_otp'])->name('login.send_otp')->middleware(ValidateCaptcha::class);

    Route::get('resend/otp', [AuthenticatedSessionController::class, 'resend_otp'])->name('resend_otp')->middleware(ValidateCaptcha::class);
    Route::get('verify/otp', [AuthenticatedSessionController::class, 'verify_otp_view'])->name('login.verify_otp_view');
    Route::post('verify/otp', [AuthenticatedSessionController::class, 'verify_otp'])->name('login.verify_otp')->middleware(ValidateCaptcha::class);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('password/code/verify', [PasswordResetLinkController::class, 'passwordResetCodeVerify'])->name('password.verify.code');
    Route::post('password/code/verify', [PasswordResetLinkController::class, 'emailVerificationCode'])->name('email.password.verify.code');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth','maintanance')->group(function () {
    Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
Route::get('auth/google', [GoogleAuthenticatedController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleAuthenticatedController::class, 'handleGoogleCallback']);
