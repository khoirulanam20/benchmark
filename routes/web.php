<?php

use App\Http\Controllers\Admin\ModelPresetController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\ScoreValidationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BenchmarkController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Benchmarks
    Route::get('benchmarks/create', [BenchmarkController::class, 'create'])->name('benchmarks.create');
    Route::post('benchmarks', [BenchmarkController::class, 'store'])->name('benchmarks.store');
    Route::get('benchmarks/{benchmark}', [BenchmarkController::class, 'show'])->name('benchmarks.show');
    Route::get('benchmarks/{benchmark}/status', [BenchmarkController::class, 'status'])->name('benchmarks.status');

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // API Keys
    Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('models', [ModelPresetController::class, 'index'])->name('models.index');
    Route::get('models/create', [ModelPresetController::class, 'create'])->name('models.create');
    Route::post('models', [ModelPresetController::class, 'store'])->name('models.store');
    Route::get('models/{model}/edit', [ModelPresetController::class, 'edit'])->name('models.edit');
    Route::put('models/{model}', [ModelPresetController::class, 'update'])->name('models.update');
    Route::delete('models/{model}', [ModelPresetController::class, 'destroy'])->name('models.destroy');

    Route::get('pricing', [PricingController::class, 'index'])->name('pricing.index');
    Route::put('pricing/{model}', [PricingController::class, 'update'])->name('pricing.update');

    Route::get('scores', [ScoreValidationController::class, 'index'])->name('scores.index');
    Route::put('scores/{result}', [ScoreValidationController::class, 'update'])->name('scores.update');
});
