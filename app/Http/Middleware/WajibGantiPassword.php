<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WajibGantiPassword
{
    // Tugas: Menghentikan permintaan pelanggan yang masih wajib mengganti password.
    // Kenapa: Pemeriksaan server tetap berlaku meskipun aplikasi mencoba melewati layar ganti password.
    // Contoh: Nilai wajib_ganti_password true menghasilkan respons 403.
    public function handle(Request $request, Closure $next): Response
    {
        // Pelanggan yang password-nya baru direset Kasir wajib menggantinya dulu.
        // Dicek di server, jadi tidak bisa dilewati dari APK.
        if ($request->user()->wajib_ganti_password) {
            return response()->json([
                'pesan' => 'Anda wajib mengganti password sementara.',
                'wajib_ganti_password' => true,
            ], 403);
        }

        return $next($request);
    }
}