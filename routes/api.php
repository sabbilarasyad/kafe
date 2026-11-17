<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PesananController;
use Illuminate\Support\Facades\Route;

Route::post('daftar', [AuthController::class, 'daftar'])->middleware('throttle:20,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:20,1');

// Semua di bawah ini: harus login (token) dan berperan pelanggan.
Route::middleware(['auth:sanctum', 'peran:pelanggan'])->group(function () {
    Route::post('ganti-password', [AuthController::class, 'gantiPassword']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Fitur lain: hanya boleh kalau password sementara sudah diganti.
    Route::middleware('wajib.ganti')->group(function () {
        Route::get('menu', [MenuController::class, 'index']);
        Route::post('pesanan', [PesananController::class, 'store']);
    });
});