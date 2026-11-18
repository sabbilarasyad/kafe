<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BahanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\StokMasukController;
use App\Http\Controllers\HppController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ImporController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/uji-manajer', fn () => 'Halo Manajer')->middleware(['auth', 'peran:manajer']);
Route::get('/uji-kasir', fn () => 'Halo Kasir')->middleware(['auth', 'peran:kasir']);

Route::middleware(['auth', 'peran:manajer'])->group(function () {
    Route::resource('bahan', BahanController::class)->except(['show', 'destroy']);

    Route::resource('menu', MenuController::class)->except(['edit', 'destroy']);
    Route::patch('menu/{menu}/aktif', [MenuController::class, 'ubahAktif']);

    Route::get('resep', [ResepController::class, 'index']);
    Route::get('resep/{menu}', [ResepController::class, 'show']);
    Route::get('resep/{menu}/tambah', [ResepController::class, 'create']);
    Route::post('resep/{menu}', [ResepController::class, 'store']);
    Route::get('resep/item/{resepItem}/edit', [ResepController::class, 'edit']);
    Route::put('resep/item/{resepItem}', [ResepController::class, 'update']);
    Route::delete('resep/item/{resepItem}', [ResepController::class, 'destroy']);
    Route::get('stok-masuk', [StokMasukController::class, 'index']);
    Route::post('stok-masuk', [StokMasukController::class, 'store']);
    Route::get('hpp', [HppController::class, 'index']);
    Route::get('laporan', [LaporanController::class, 'index']);
    Route::get('impor', [ImporController::class, 'index']);
    Route::post('impor', [ImporController::class, 'jalankan']);
});

Route::middleware(['auth', 'peran:kasir'])->group(function () {
    Route::get('pesanan/create', [PesananController::class, 'create']);
    Route::post('pesanan', [PesananController::class, 'store']);
    Route::get('pesanan/{pesanan}/struk', [PesananController::class, 'struk']);
    Route::get('pesanan', [PesananController::class, 'index']);
    Route::post('pesanan/{pesanan}/status', [PesananController::class, 'ubahStatus']);
    Route::post('pesanan/{pesanan}/batal', [PesananController::class, 'batal']);
    Route::get('reset-password', [ResetPasswordController::class, 'index']);
    Route::post('reset-password', [ResetPasswordController::class, 'reset']);
});

Route::middleware('auth')->group(function () {
    Route::get('ganti-password', [AuthController::class, 'formGantiPassword'])->name('ganti-password');
    Route::post('ganti-password', [AuthController::class, 'gantiPassword']);
});

Route::get('/dashboard', function () {
    return request()->user()->role === 'kasir'
        ? redirect('/pesanan/create')
        : redirect('/bahan');
})->middleware('auth');