<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Inventory\UnitOfMeasureController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::apiResource('users', UserController::class);

        Route::prefix('inventory')->group(function () {
            Route::apiResource('units', UnitOfMeasureController::class);
            Route::post('/units/{unit}/activate', [UnitOfMeasureController::class, 'activate'])->name('units.activate');
            Route::post('/units/{unit}/deactivate', [UnitOfMeasureController::class, 'deactivate'])->name('units.deactivate');
        });
        
    });
});
