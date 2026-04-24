<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Token\TokenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service — API Routes
|--------------------------------------------------------------------------
|
| Public:          /api/auth/register   /api/auth/login
| JWT-protected:   /api/auth/logout     /api/auth/refresh   /api/auth/me
| Service-secret:  /api/token/validate  /api/token/public-key
|
*/

// ─── Auth endpoints ──────────────────────────────────────────────────────────

Route::prefix('auth')->group(function () {
    Route::get('check', function () {
        return response()->json(['message' => 'Auth service is running']);
    });
    // Public — no auth required
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login',    [AuthController::class,    'login']);

    // Protected — valid JWT required
    Route::middleware('jwt.auth')->group(function () {
        Route::post('logout',  [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me',       [AuthController::class, 'me']);
    });
});

// ─── Inter-service token endpoints ───────────────────────────────────────────
//
// Called by Event, Kiosk, Tournament services — not by end users.
// Protected by X-Service-Secret header (InternalServiceMiddleware).

Route::prefix('token')->middleware('internal.service')->group(function () {
    Route::post('validate',   [TokenController::class, 'validate']);
    Route::get('public-key',  [TokenController::class, 'publicKey']);
});
