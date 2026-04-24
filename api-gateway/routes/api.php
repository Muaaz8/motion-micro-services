<?php

use App\Http\Controllers\ProxyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;

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
// Role management — admin only
Route::prefix('roles')->middleware('jwt.auth')->group(function () {
    Route::get('/',                [RoleController::class, 'index']);
    Route::post('/',               [RoleController::class, 'store']);
    Route::get('/{id}',            [RoleController::class, 'show']);
    Route::put('/{id}',            [RoleController::class, 'update']);
    Route::delete('/{id}',         [RoleController::class, 'destroy']);
    Route::post('/{id}/assign',    [RoleController::class, 'assign']);
    Route::post('/{id}/revoke',    [RoleController::class, 'revoke']);
});

Route::get('/permissions', [RoleController::class, 'permissions'])->middleware('jwt.auth');
