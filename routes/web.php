<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapCollectionController;
use App\Http\Controllers\PointController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('geocode/search', [GeocodeController::class, 'search'])
        ->name('geocode.search');

    Route::resource('collections', MapCollectionController::class);

    Route::get('collections/{collection}/attributes', [AttributeController::class, 'index'])
        ->name('collections.attributes.index');
    Route::post('collections/{collection}/attributes', [AttributeController::class, 'store'])
        ->name('collections.attributes.store');
    Route::put('collections/{collection}/attributes/{attribute}', [AttributeController::class, 'update'])
        ->name('collections.attributes.update');
    Route::delete('collections/{collection}/attributes/{attribute}', [AttributeController::class, 'destroy'])
        ->name('collections.attributes.destroy');
    Route::patch('collections/{collection}/attributes/{attribute}/visibility', [AttributeController::class, 'toggleVisibility'])
        ->name('collections.attributes.visibility');

    Route::get('collections/{collection}/points', [PointController::class, 'index'])
        ->name('collections.points.index');
    Route::post('collections/{collection}/points', [PointController::class, 'store'])
        ->name('collections.points.store');
    Route::put('collections/{collection}/points/{point}', [PointController::class, 'update'])
        ->name('collections.points.update');
    Route::delete('collections/{collection}/points/{point}', [PointController::class, 'destroy'])
        ->name('collections.points.destroy');

    Route::get('collections/{collection}/export/{format?}', [MapCollectionController::class, 'export'])
        ->where('format', 'json|csv')
        ->name('collections.export');
});
