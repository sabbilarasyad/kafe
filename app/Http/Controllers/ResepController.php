<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\Menu;
use App\Models\ResepItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResepController extends Controller
{
    // Semua menu, plus jumlah baris resepnya (untuk lencana "Belum ada resep").
    private function daftar()
    {
        return Menu::withCount('resepItems')->orderBy('nama')->get();
    }

    private function tampil(Menu $menu)
    {
        return view('resep.index', [
            'daftar' => $this->daftar(),
            'terpilih' => $menu->load('resepItems.bahan'),
        ]);
    }

    // Buka halaman: menu pertama langsung terpilih.
    public function index()
    {
        $menu = Menu::orderBy('nama')->first();

        return $menu ? $this->tampil($menu) : redirect('/menu')->with('status', 'Tambah menu dulu sebelum mengisi resep.');
    }

    // Resep satu menu.
    public function show(Menu $menu)
    {
        return $this->tampil($menu);
    }

    // Form tambah bahan ke resep.
    public function create(Menu $menu)
    {
        // Hanya bahan yang belum ada di resep menu ini.
        $bahan = Bahan::whereNotIn('id', $menu->resepItems()->pluck('bahan_id'))
            ->orderBy('nama')
            ->get();

        return view('resep.form', ['menu' => $menu, 'item' => new ResepItem(), 'bahan' => $bahan]);
    }

    public function store(Request $request, Menu $menu)
    {
        $data = $request->validate([
            // Bahan harus ada, dan belum dipakai di resep menu ini.
            'bahan_id' => [
                'required',
                'exists:bahan,id',
                Rule::unique('resep_item', 'bahan_id')->where('menu_id', $menu->id),
            ],
            'takaran' => ['required', 'numeric', 'gt:0'],
        ], [
            'bahan_id.unique' => 'Bahan itu sudah ada di resep.',
        ]);

        $menu->resepItems()->create($data);

        return redirect('/resep/' . $menu->id)->with('status', 'Bahan ditambahkan ke resep.');
    }

    // Form ubah takaran (bahan tidak bisa diganti, hanya takarannya).
    public function edit(ResepItem $resepItem)
    {
        return view('resep.form', [
            'menu' => $resepItem->menu,
            'item' => $resepItem,
            'bahan' => collect(),
        ]);
    }

    public function update(Request $request, ResepItem $resepItem)
    {
        $data = $request->validate([
            'takaran' => ['required', 'numeric', 'gt:0'],
        ]);
        $resepItem->update($data);

        return redirect('/resep/' . $resepItem->menu_id)->with('status', 'Takaran diperbarui.');
    }

    // Hapus satu bahan dari resep. Pesanan lama aman: yang tersimpan di
    // pesanan_bahan adalah bahan dan jumlah yang terpakai, bukan baris resep.
    public function destroy(ResepItem $resepItem)
    {
        $menuId = $resepItem->menu_id;
        $resepItem->delete();

        return redirect('/resep/' . $menuId)->with('status', 'Bahan dihapus dari resep.');
    }
}