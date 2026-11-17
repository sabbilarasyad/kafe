<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    // Kategori menu sesuai data klien: Minuman dan Makanan.
    public const KATEGORI = ['Minuman', 'Makanan'];

    // Semua menu, plus jumlah baris resepnya (untuk lencana "Belum ada resep").
    private function daftar()
    {
        return Menu::withCount('resepItems')->orderBy('nama')->get();
    }

    private function tampil(Menu $terpilih)
    {
        return view('menu.index', [
            'daftar' => $this->daftar(),
            'terpilih' => $terpilih,
            'kategori' => self::KATEGORI,
        ]);
    }

    // Buka halaman: menu pertama langsung terpilih.
    public function index()
    {
        return $this->tampil($this->daftar()->first() ?? new Menu(['aktif' => true]));
    }

    // Detail satu menu.
    public function show(Menu $menu)
    {
        return $this->tampil($menu);
    }

    // Form menu baru (memakai halaman yang sama).
    public function create()
    {
        return $this->tampil(new Menu(['aktif' => true]));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['aktif'] = true;
        $menu = Menu::create($data);

        return redirect('/menu/' . $menu->id)->with('status', 'Menu ditambahkan.');
    }

    public function update(Request $request, Menu $menu)
    {
        $menu->update($this->validasi($request, $menu));

        return redirect('/menu/' . $menu->id)->with('status', 'Menu diperbarui.');
    }

    // Nyalakan atau matikan menu. Pesanan lama tidak terpengaruh.
    public function ubahAktif(Menu $menu)
    {
        $menu->update(['aktif' => ! $menu->aktif]);

        return back();
    }

    private function validasi(Request $request, ?Menu $menu = null): array
    {
        // Buang spasi di awal/akhir nama sebelum diperiksa.
        $request->merge(['nama' => trim((string) $request->input('nama'))]);

        return $request->validate([
            'nama' => [
                'required', 'string', 'max:100',
                Rule::unique('menu', 'nama')->ignore($menu?->id),
            ],
            'kategori' => ['required', Rule::in(self::KATEGORI)],
            'harga' => ['required', 'integer', 'min:1'],
        ]);
    }
}