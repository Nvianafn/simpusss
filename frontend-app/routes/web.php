<?php

use App\Http\Controllers\AdminPembayaranController;
use App\Http\Controllers\AdminPplController;
use App\Http\Controllers\AuthSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PplRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/login', [AuthSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthSessionController::class, 'destroy'])->name('logout');

Route::middleware('simpus.role:mahasiswa,super_admin')->group(function (): void {
    Route::get('/portal', PortalController::class)->name('portal');
    Route::post('/portal/ppl-daftar', [PplRegistrationController::class, 'store'])->name('portal.ppl-daftar');
});

Route::middleware('simpus.role:super_admin,admin_ppl')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/ppl', [AdminPplController::class, 'index'])->name('ppl');
    Route::post('/ppl/{id}/approve', [AdminPplController::class, 'approve'])->whereNumber('id')->name('ppl.approve');
    Route::post('/ppl/{id}/reject', [AdminPplController::class, 'reject'])->whereNumber('id')->name('ppl.reject');
});

Route::middleware('simpus.role:super_admin,admin_bank')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran');
    Route::post('/pembayaran/{id}/konfirmasi', [AdminPembayaranController::class, 'confirm'])->whereNumber('id')->name('pembayaran.konfirmasi');
});
