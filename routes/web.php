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

    Route::get('menu/{menu}/resep/create', [ResepController::class, 'create']);
    Route::post('menu/{menu}/resep', [ResepController::class, 'store']);
    Route::get('resep/{resepItem}/edit', [ResepController::class, 'edit']);
    Route::put('resep/{resepItem}', [ResepController::class, 'update']);
    Route::get('stok-masuk', [StokMasukController::class, 'index']);
    Route::post('stok-masuk', [StokMasukController::class, 'store']);
    Route::get('hpp', [HppController::class, 'index']);
    Route::get('laporan', [LaporanController::class, 'index']);
});

Route::middleware(['auth', 'peran:kasir'])->group(function () {
    Route::get('pesanan/create', [PesananController::class, 'create']);
    Route::post('pesanan', [PesananController::class, 'store']);
    Route::get('pesanan/{pesanan}/struk', [PesananController::class, 'struk']);
    Route::get('pesanan', [PesananController::class, 'index']);
    Route::post('pesanan/{pesanan}/status', [PesananController::class, 'ubahStatus']);
    Route::post('pesanan/{pesanan}/batal', [PesananController::class, 'batal']);
});

Route::resource('menu', MenuController::class)->except(['edit', 'destroy']);
Route::patch('menu/{menu}/aktif', [MenuController::class, 'ubahAktif']);

Route::get('/dashboard', function () {
    return request()->user()->role === 'kasir'
        ? redirect('/pesanan/create')
        : redirect('/bahan');
})->middleware('auth');