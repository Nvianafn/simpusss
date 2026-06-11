<?php

declare(strict_types=1);

use App\Http\Controllers\Api\PplController;
use App\Http\Middleware\VerifyInternalToken;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyInternalToken::class)->group(function (): void {
    Route::get('/ppl/status/{nim}', [PplController::class, 'status']);
    Route::get('/ppl/pendaftaran', [PplController::class, 'index']);
    Route::post('/ppl/daftar', [PplController::class, 'daftar']);
    Route::put('/ppl/pendaftaran/{id}/approve', [PplController::class, 'approve'])->whereNumber('id');
    Route::put('/ppl/pendaftaran/{id}/reject', [PplController::class, 'reject'])->whereNumber('id');
});
