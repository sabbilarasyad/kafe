<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\KetersediaanMenu;

class MenuController extends Controller
{
    public function index()
    {
        $daftar = Menu::with('resepItems.bahan')
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        return response()->json($daftar->map(function (Menu $m) {
            return [
                'id' => $m->id,
                'nama' => $m->nama,
                'kategori' => $m->kategori,
                'harga' => (int) $m->harga,
                'sisa_porsi' => KetersediaanMenu::sisaPorsi($m) ?? 0,
                'habis' => KetersediaanMenu::habis($m),
            ];
        })->values());
    }
}