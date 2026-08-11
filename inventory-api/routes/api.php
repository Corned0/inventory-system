<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Inventory\UnitOfMeasureController;
use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemTypeController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::apiResource('users', UserController::class);

        Route::prefix('inventory')->group(function () {
            Route::apiResource('units', UnitOfMeasureController::class)->only(['index','store','show','update',]);
            Route::post('/units/{unit}/activate', [UnitOfMeasureController::class, 'activate'])->name('units.activate');
            Route::post('/units/{unit}/deactivate', [UnitOfMeasureController::class, 'deactivate'])->name('units.deactivate');
            
            Route::get('/categories/tree', [ItemCategoryController::class, 'tree'])->name('categories.tree');// 1
            Route::apiResource('categories', ItemCategoryController::class);// 2
            Route::post('/categories/{category}/activate', [ItemCategoryController::class, 'activate'])->name('categories.activate');
            Route::post('/categories/{category}/deactivate', [ItemCategoryController::class, 'deactivate'])->name('categories.deactivate');

            Route::apiResource('item-types', ItemTypeController::class)->parameters(['item-types' => 'itemType',])->only(['index','store','show','update',]);
            Route::post('item-types/{itemType}/activate',[ItemTypeController::class, 'activate'])->name('item-types.activate');
            Route::post('item-types/{itemType}/deactivate',[ItemTypeController::class, 'deactivate'])->name('item-types.deactivate');
        });
        
    });
});
