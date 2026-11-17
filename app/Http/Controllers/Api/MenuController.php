<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;

class MenuController extends Controller
{
    // Daftar menu untuk APK. Hanya menu aktif, tanpa resep, HPP, dan margin.
    public function index()
    {
        $menu = Menu::where('aktif', true)
            ->with('resepItems.bahan')
            ->orderBy('nama')
            ->get()
            ->map(function (Menu $m) {
                $porsi = $m->porsiTersedia();

                return [
                    'id' => $m->id,
                    'nama' => $m->nama,
                    'kategori' => $m->kategori,
                    'harga' => (int) $m->harga,
                    'sisa_porsi' => $porsi,
                    'habis' => $porsi < 1,   // bahan tidak cukup untuk 1 porsi, atau belum ada resep
                ];
            });

        return response()->json($menu);
    }
}