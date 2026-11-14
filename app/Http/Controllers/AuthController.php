<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //Tampilkan halaman login
    public function formLogin()
    {
        return view('login');
    }

    //Proses login
    public function login(Request $request)
    {
        //pastikan email dan password diisi
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        //cocokan dengan database, kalau salah kembali ke form login
        if (!Auth::attempt($data)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah'])
                ->withInput();
        }

        //ganti id sessio supaya aman
        $request->session()->regenerate();

        //web hanya untuk staf, kalau bukan kasir/manajer: logout dan tolak
        if (! in_array(Auth::user()->role, ['kasir', 'manajer'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Anda tidak memiliki akses ke halaman ini'])
                ->onlyInput('email');
        }

        //kalau berhasil login, redirect ke halaman dashboard
        return redirect()->intended('/dashboard');
    }

    //Proses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // Tampilkan form ganti password.
    public function formGantiPassword()
    {
        return view('ganti-password');
    }

    // Proses ganti password.
    public function gantiPassword(Request $request)
    {
        $request->validate([
            // current_password = cocok dengan password akun yang sedang login.
            'password_lama' => ['required', 'current_password'],
            // min:8 = minimal 8 karakter, confirmed = harus sama dengan password_baru_confirmation.
            'password_baru' => ['required', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return redirect('/dashboard')->with('status', 'Password berhasil diganti.');
    }
}