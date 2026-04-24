<?php

use App\Http\Controllers\ProxyController;
use Illuminate\Support\Facades\Route;

// Auth routes — no token validation needed (login, register etc.)
Route::any('/auth/{path?}', [ProxyController::class, 'forward'])
    ->defaults('service', 'auth')
    ->where('path', '.*');

// Protected routes — all require valid JWT
Route::middleware('auth.gateway')->group(function () {

    Route::any('/events/{path?}', [ProxyController::class, 'forward'])
        ->defaults('service', 'event_management')
        ->where('path', '.*');

    Route::any('/kiosk/{path?}', [ProxyController::class, 'forward'])
        ->defaults('service', 'kiosk')
        ->where('path', '.*');

    Route::any('/tournament/{path?}', [ProxyController::class, 'forward'])
        ->defaults('service', 'tournament')
        ->where('path', '.*');

});
