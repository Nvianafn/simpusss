<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class)->except(['show']);
    Route::put('/roles/{id}/permissions', [RoleController::class, 'syncPermissions']);
    Route::put('/roles/{id}/menus', [RoleController::class, 'syncMenus']);
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::get('/menus', [MenuController::class, 'index']);
    Route::post('/menus', [MenuController::class, 'store']);
});
