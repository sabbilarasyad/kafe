<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekPeran
{
    // Tugas: membatasi akses halaman berdasarkan peran pengguna.
    // Kenapa: pemeriksaan di server mencegah pengguna membuka halaman tanpa izin,
    //         termasuk lewat alamat yang diketik langsung atau lewat REST API.
    // Contoh: peran kasir ditolak (403) saat route hanya mengizinkan manajer.
    // $peran berisi daftar peran yang diizinkan, mis. ['manajer'].
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        // Tolak bila belum login atau perannya tidak ada di daftar yang diizinkan.
        if (! Auth::check() || ! in_array(Auth::user()->role, $peran, true)) {
            abort(403);
        }

        return $next($request);
    }
}