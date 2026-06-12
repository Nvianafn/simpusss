<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminKlinikController;
use App\Http\Controllers\AdminMahasiswaController;
use App\Http\Controllers\AdminPembayaranController;
use App\Http\Controllers\AdminPplController;
use App\Http\Controllers\AuthSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PplRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/dashboard', DashboardController::class)->name('mahasiswa.dashboard');
Route::get('/login', [AuthSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthSessionController::class, 'destroy'])->name('logout');

Route::middleware('simpus.role:mahasiswa,super_admin')->prefix('portal')->name('portal.')->group(function (): void {
    Route::get('/', [PortalController::class, 'index'])->name('index');
    Route::get('/mahasiswa', [PortalController::class, 'mahasiswa'])->name('mahasiswa');
    Route::get('/klinik', [PortalController::class, 'klinik'])->name('klinik');
    Route::get('/bank', [PortalController::class, 'bank'])->name('bank');
    Route::get('/ppl', [PortalController::class, 'ppl'])->name('ppl');
    Route::post('/ppl-daftar', [PplRegistrationController::class, 'store'])->name('ppl-daftar');
});

Route::get('/portal', [PortalController::class, 'index'])->middleware('simpus.role:mahasiswa,super_admin')->name('portal');

Route::middleware('simpus.role:super_admin')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [AdminDashboardController::class, 'global'])->name('dashboard');
});

Route::middleware('simpus.role:super_admin,admin_ppl')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/ppl/dashboard', [AdminDashboardController::class, 'ppl'])->name('ppl.dashboard');
    Route::get('/ppl', [AdminPplController::class, 'index'])->name('ppl');
    Route::post('/ppl/{id}/approve', [AdminPplController::class, 'approve'])->whereNumber('id')->name('ppl.approve');
    Route::post('/ppl/{id}/reject', [AdminPplController::class, 'reject'])->whereNumber('id')->name('ppl.reject');
});

Route::middleware('simpus.role:super_admin,admin_mahasiswa')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/mahasiswa/dashboard', [AdminDashboardController::class, 'mahasiswa'])->name('mahasiswa.dashboard');
    Route::get('/mahasiswa', [AdminMahasiswaController::class, 'index'])->name('mahasiswa');
    Route::post('/mahasiswa', [AdminMahasiswaController::class, 'store'])->name('mahasiswa.store');
    Route::put('/mahasiswa/{nim}', [AdminMahasiswaController::class, 'update'])->name('mahasiswa.update');
    Route::delete('/mahasiswa/{nim}', [AdminMahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
});

Route::middleware('simpus.role:super_admin,admin_klinik')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/klinik/dashboard', [AdminDashboardController::class, 'klinik'])->name('klinik.dashboard');
    Route::get('/klinik', [AdminKlinikController::class, 'index'])->name('klinik');
    Route::post('/klinik', [AdminKlinikController::class, 'store'])->name('klinik.store');
    Route::put('/klinik/{id}', [AdminKlinikController::class, 'update'])->whereNumber('id')->name('klinik.update');
});

Route::middleware('simpus.role:super_admin,admin_bank')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/bank/dashboard', [AdminDashboardController::class, 'bank'])->name('bank.dashboard');
    Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran');
    Route::post('/pembayaran', [AdminPembayaranController::class, 'store'])->name('pembayaran.store');
    Route::post('/pembayaran/{id}/konfirmasi', [AdminPembayaranController::class, 'confirm'])->whereNumber('id')->name('pembayaran.konfirmasi');
});
