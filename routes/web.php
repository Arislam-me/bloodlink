<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\PhoneVerificationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationHistoryController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\EmergencyRequestController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/privacy-policy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');

// Location lookups for dependent dropdowns.
Route::middleware('throttle:locations')->prefix('api/locations')->name('locations.')->group(function () {
    Route::get('divisions/{division}/districts', [LocationController::class, 'districts'])->whereNumber('division')->name('districts');
    Route::get('districts/{district}/upazilas', [LocationController::class, 'upazilas'])->whereNumber('district')->name('upazilas');
    Route::get('upazilas/{upazila}/areas', [LocationController::class, 'areas'])->whereNumber('upazila')->name('areas');
});

// Donor search & public (masked) donor details.
Route::middleware('throttle:search')->group(function () {
    Route::get('/donors', [DonorController::class, 'index'])->name('donors.index');
    Route::get('/donors/{donor}', [DonorController::class, 'show'])->name('donors.show');
    Route::get('/emergency', [EmergencyRequestController::class, 'index'])->name('emergency.index');
});

/*
|--------------------------------------------------------------------------
| Guest authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:auth');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:auth');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:auth')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:auth')->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated users
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Mobile OTP verification (the only account verification step).
    Route::get('/verify-phone', [PhoneVerificationController::class, 'notice'])->name('phone.verify.notice');
    Route::post('/verify-phone/send', [PhoneVerificationController::class, 'send'])->middleware('throttle:otp')->name('phone.verify.send');
    Route::post('/verify-phone', [PhoneVerificationController::class, 'verify'])->middleware('throttle:10,1')->name('phone.verify');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile management (works before verification so users can fix a mistyped phone number).
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/deactivate', [ProfileController::class, 'deactivate'])->name('profile.deactivate');
    Route::post('/profile/deletion-request', [ProfileController::class, 'requestDeletion'])->name('profile.deletion');

    Route::get('/donations', [DonationHistoryController::class, 'index'])->name('donations.index');
    Route::post('/donations', [DonationHistoryController::class, 'store'])->name('donations.store');
    // Donors can't delete history themselves; they ask an admin to remove an entry.
    Route::post('/donations/{donation}/removal-request', [DonationHistoryController::class, 'requestRemoval'])->name('donations.removal-request');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->whereUuid('id')->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Privacy-sensitive features require a verified mobile number.
    Route::middleware('phone.verified')->group(function () {
        // Unlocking shows the donor's phone number; limited to N unique donors per month.
        Route::post('/donors/{donor}/unlock', [DonorController::class, 'unlock'])->middleware('throttle:profile-unlock')->name('donors.unlock');

        Route::get('/emergency/create', [EmergencyRequestController::class, 'create'])->name('emergency.create');
        Route::post('/emergency', [EmergencyRequestController::class, 'store'])->middleware('throttle:emergency')->name('emergency.store');
        Route::post('/emergency/{emergency}/respond', [EmergencyRequestController::class, 'respond'])->middleware('throttle:contact-request')->name('emergency.respond');
        Route::post('/emergency/{emergency}/fulfil', [EmergencyRequestController::class, 'fulfil'])->name('emergency.fulfil');
        Route::post('/emergency/{emergency}/cancel', [EmergencyRequestController::class, 'cancel'])->name('emergency.cancel');

        Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:report')->name('reports.store');
    });
});

// Must come after /emergency/create so "create" isn't captured as a request id.
Route::get('/emergency/{emergency}', [EmergencyRequestController::class, 'show'])->middleware('throttle:search')->name('emergency.show');
