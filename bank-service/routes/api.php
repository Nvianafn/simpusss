<?php

declare(strict_types=1);

use App\Http\Controllers\Api\PembayaranController;
use App\Http\Middleware\VerifyInternalToken;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyInternalToken::class)->group(function (): void {
    Route::get('/pembayaran', [PembayaranController::class, 'index']);
    Route::get('/pembayaran/{nim}/status', [PembayaranController::class, 'status']);
    Route::get('/pembayaran/{nim}', [PembayaranController::class, 'byNim']);
    Route::post('/pembayaran', [PembayaranController::class, 'store']);
    Route::put('/pembayaran/{id}/konfirmasi', [PembayaranController::class, 'confirm'])->whereNumber('id');
});
