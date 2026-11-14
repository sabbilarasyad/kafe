<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BahanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ResepController;

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
});

Route::resource('menu', MenuController::class)->except(['edit', 'destroy']);
Route::patch('menu/{menu}/aktif', [MenuController::class, 'ubahAktif']);


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');