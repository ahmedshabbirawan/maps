<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\MapCollectionController;
use App\Http\Controllers\PointController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('collections.index')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('geocode/search', [GeocodeController::class, 'search'])
        ->name('geocode.search');

    Route::resource('collections', MapCollectionController::class);

    Route::post('collections/{collection}/attributes', [MapCollectionController::class, 'storeAttribute'])
        ->name('collections.attributes.store');
    Route::delete('collections/{collection}/attributes/{attribute}', [MapCollectionController::class, 'destroyAttribute'])
        ->name('collections.attributes.destroy');

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
