<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BahanController extends Controller
{
    // Daftar semua bahan.
    public function index()
    {
        $bahan = Bahan::orderBy('nama')->get();

        return view('bahan.index', compact('bahan'));
    }

    // Form tambah bahan.
    public function create()
    {
        return view('bahan.form', ['bahan' => new Bahan()]);
    }

    // Simpan bahan baru.
    public function store(Request $request)
    {
        Bahan::create($this->validasi($request));

        return redirect('/bahan')->with('status', 'Bahan ditambahkan.');
    }

    // Form ubah bahan.
    public function edit(Bahan $bahan)
    {
        return view('bahan.form', compact('bahan'));
    }

    // Simpan perubahan bahan.
    public function update(Request $request, Bahan $bahan)
    {
        $bahan->update($this->validasi($request, $bahan->id));

        return redirect('/bahan')->with('status', 'Bahan diperbarui.');
    }

    // Aturan input. $id dipakai saat ubah, supaya bahan itu
    // tidak dianggap ganda dengan dirinya sendiri.
    private function validasi(Request $request, ?int $id = null): array
    {
        // nama_kunci = nama tanpa spasi tepi, huruf kecil semua.
        // Inilah yang membuat "Susu UHT " dan "susu uht" dianggap sama.
        $request->merge([
            'nama' => trim((string) $request->nama),
            'nama_kunci' => strtolower(trim((string) $request->nama)),
        ]);

        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nama_kunci' => ['required', Rule::unique('bahan', 'nama_kunci')->ignore($id)],
            'satuan' => ['required', Rule::in(['g', 'ml', 'pcs'])],
            'batas_minimum' => ['required', 'numeric', 'min:0'],
        ], [
            'nama_kunci.unique' => 'Nama bahan sudah ada.',
        ]);
    }
}