<?php

namespace App\Http\Controllers;

use App\Models\Menu;

class PesananController extends Controller
{
    // Halaman Kasir membuat pesanan: hanya menu yang aktif.
    public function create()
    {
        $menu = Menu::with('resepItems.bahan')
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        return view('pesanan.create', compact('menu'));
    }
}