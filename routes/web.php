<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/uji-manajer', fn () => 'Halo Manajer')->middleware(['auth', 'peran:manajer']);
Route::get('/uji-kasir', fn () => 'Halo Kasir')->middleware(['auth', 'peran:kasir']);


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');