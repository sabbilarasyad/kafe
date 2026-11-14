<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekPeran
{
    // $peran berisi daftar peran yang diizinkan, mis. ['manajer'].
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        // BAGIANMU: kalau role user yang login TIDAK ada di daftar $peran,
        // hentikan dengan abort(403).
        // Petunjuk: bentuknya sama dengan pengecekan di AuthController,
        // yaitu in_array(Auth::user()->role, $peran, true) dan tanda "!".
        if (!Auth::check() || !in_array(Auth::user()->role, $peran, true)) {
            abort(403);
        }
        return $next($request);
    }
}