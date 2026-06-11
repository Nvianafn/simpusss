<?php

declare(strict_types=1);

use App\Http\Controllers\Api\MahasiswaController;
use App\Http\Middleware\VerifyInternalToken;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyInternalToken::class)->group(function (): void {
    Route::get('/mahasiswa/{nim}/status', [MahasiswaController::class, 'status']);

    Route::apiResource('mahasiswa', MahasiswaController::class)
        ->parameters(['mahasiswa' => 'nim']);
});
