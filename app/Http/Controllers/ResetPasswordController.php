<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    // Form cari pelanggan, dan hasil pencarian.
    public function index(Request $request)
    {
        $pelanggan = null;
        $nomor = preg_replace('/\D/', '', (string) $request->query('nomor_hp')); // hanya angka
        $dicari = $nomor !== '';

        if ($dicari) {
            // Hanya akun pelanggan yang boleh direset oleh kasir (bukan akun staf).
            $pelanggan = User::where('role', 'pelanggan')->where('nomor_hp', $nomor)->first();
        }

        return view('reset-password.index', compact('pelanggan', 'dicari'));
    }

    // Reset password sementara.
    public function reset(Request $request)
    {
        $data = $request->validate(['nomor_hp' => ['required', 'string']]);

        $pelanggan = User::where('role', 'pelanggan')
            ->where('nomor_hp', $data['nomor_hp'])
            ->firstOrFail();

        // Password sementara acak, 8 karakter.
        $sementara = Str::random(8);

        $pelanggan->update([
            'password' => Hash::make($sementara),
            'wajib_ganti_password' => true, // pelanggan wajib menggantinya saat login berikutnya
        ]);

        // flash: password hanya tampil sekali, tidak disimpan dalam bentuk asli.
        return redirect('/reset-password?nomor_hp=' . urlencode($pelanggan->nomor_hp))
            ->with('sementara', $sementara);
    }
}
