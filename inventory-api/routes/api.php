<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Exceptions\InsufficientStockException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('users', UserController::class);
    });
});

Route::get('/test-error', function () {
    abort(404, 'Test resource not found.');
});

Route::get('/test-exception', function () {
    throw new InsufficientStockException(
        available: 5,
        requested: 10,
    );
});

Route::post('/test-validation', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string'],
        'quantity' => ['required', 'integer', 'min:1'],
    ]);

    return response()->json([
        'data' => $validated,
    ]);
});

Route::middleware('auth:sanctum')->get('/test-forbidden', function () {
    Gate::authorize('test-permission');

    return response()->json([
        'data' => [
            'message' => 'You have permission.',
        ],
    ]);
});