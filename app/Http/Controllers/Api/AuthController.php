<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Pelanggan mendaftar sendiri dengan nomor HP dan password.
    // Tugas: Membuat akun pelanggan dan mengembalikan token untuk APK.
    // Kenapa: Password disimpan dalam bentuk hash agar teks aslinya tidak disimpan.
    // Contoh: Nomor 081234567890 yang valid dapat didaftarkan dengan password 8 karakter.
    public function daftar(Request $request)
    {
        $data = $request->validate([
            // Hanya angka, diawali 08, total 10 sampai 15 digit. Huruf otomatis ditolak.
            'nomor_hp' => ['required', 'regex:/^08[0-9]{8,13}$/', 'unique:users,nomor_hp'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'nomor_hp.regex' => 'Nomor HP hanya angka, diawali 08, 10 sampai 15 digit.',
            'nomor_hp.unique' => 'Nomor HP sudah terdaftar.',
        ]);

        $user = User::create([
            'name' => $data['nomor_hp'],      // desain pendaftaran tidak punya kolom nama
            'nomor_hp' => $data['nomor_hp'],
            'password' => Hash::make($data['password']),
            'role' => 'pelanggan',
        ]);

        return response()->json([
            'token' => $user->createToken('apk')->plainTextToken,
            'wajib_ganti_password' => false,
        ], 201);
    }

    // Login dengan nomor HP. Hanya akun pelanggan.
    // Tugas: Memeriksa kredensial pelanggan dan membuat token masuk.
    // Kenapa: Pesan gagal dibuat sama agar tidak membocorkan apakah nomor terdaftar.
    // Contoh: Nomor dan password yang cocok menghasilkan token.
    public function login(Request $request)
    {
        $data = $request->validate([
            'nomor_hp' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('role', 'pelanggan')->where('nomor_hp', $data['nomor_hp'])->first();

        // Pesan sama untuk nomor salah maupun password salah.
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['nomor_hp' => 'Nomor HP atau password salah.']);
        }

        return response()->json([
            'token' => $user->createToken('apk')->plainTextToken,
            'wajib_ganti_password' => (bool) $user->wajib_ganti_password,
        ]);
    }

    // Ganti password (juga dipakai untuk mengganti password sementara).
    // Tugas: Mengganti password pelanggan setelah password lama terbukti benar.
    // Kenapa: Konfirmasi password baru membantu mencegah salah ketik saat mengganti.
    // Contoh: Password baru minimal 8 karakter dan harus cocok dengan konfirmasinya.
    public function gantiPassword(Request $request)
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            // different: password baru tidak boleh sama dengan yang lama (termasuk yang sementara).
            'password_baru' => ['required', 'string', 'min:8', 'confirmed', 'different:password_lama'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password_baru),
            'wajib_ganti_password' => false,
        ]);

        return response()->json(['pesan' => 'Password berhasil diganti.']);
    }

    // Logout: hapus token yang sedang dipakai.
    // Tugas: Menghapus token APK yang sedang digunakan untuk masuk.
    // Kenapa: Token yang dihapus tidak lagi dapat dipakai untuk mengakses API.
    // Contoh: Token aktif akun A dihapus saat akun A keluar.
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['pesan' => 'Berhasil keluar.']);
    }
}