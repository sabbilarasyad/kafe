<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}