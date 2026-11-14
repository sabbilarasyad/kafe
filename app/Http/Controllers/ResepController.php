<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\Menu;
use App\Models\ResepItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResepController extends Controller
{
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

        return redirect('/menu/' . $menu->id)->with('status', 'Bahan ditambahkan ke resep.');
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

        return redirect('/menu/' . $resepItem->menu_id)->with('status', 'Takaran diperbarui.');
    }
}