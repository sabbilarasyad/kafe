<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // Semua menu, plus jumlah baris resepnya (untuk lencana "Belum ada resep").
    private function daftar()
    {
        return Menu::withCount('resepItems')->orderBy('nama')->get();
    }

    // Buka halaman: menu pertama langsung terpilih.
    public function index()
    {
        $daftar = $this->daftar();

        return view('menu.index', [
            'daftar' => $daftar,
            'terpilih' => $daftar->first() ?? new Menu(['aktif' => true]),
        ]);
    }

    // Detail satu menu.
    public function show(Menu $menu)
    {
        return view('menu.index', ['daftar' => $this->daftar(), 'terpilih' => $menu]);
    }

    // Form menu baru (memakai halaman yang sama).
    public function create()
    {
        return view('menu.index', [
            'daftar' => $this->daftar(),
            'terpilih' => new Menu(['aktif' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $menu = Menu::create($this->validasi($request));

        return redirect('/menu/' . $menu->id)->with('status', 'Menu ditambahkan.');
    }

    public function update(Request $request, Menu $menu)
    {
        $menu->update($this->validasi($request));

        return redirect('/menu/' . $menu->id)->with('status', 'Menu diperbarui.');
    }

    // Nyalakan atau matikan menu. Pesanan lama tidak terpengaruh.
    public function ubahAktif(Menu $menu)
    {
        $menu->update(['aktif' => ! $menu->aktif]);

        return back();
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'integer', 'min:1'],
        ]);
    }
}