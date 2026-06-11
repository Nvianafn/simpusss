<?php

declare(strict_types=1);

use App\Http\Controllers\Api\KesehatanController;
use App\Http\Middleware\VerifyInternalToken;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyInternalToken::class)->group(function (): void {
    Route::get('/kesehatan/{nim}', [KesehatanController::class, 'latest']);
    Route::get('/kesehatan/{nim}/riwayat', [KesehatanController::class, 'history']);
    Route::post('/kesehatan', [KesehatanController::class, 'store']);
    Route::put('/kesehatan/{id}', [KesehatanController::class, 'update'])->whereNumber('id');
});
