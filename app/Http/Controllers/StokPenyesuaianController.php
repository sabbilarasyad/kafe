<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\StokPenyesuaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StokPenyesuaianController extends Controller
{
    // Form penyesuaian + riwayat terbaru.
    public function index()
    {
        return view('stok-penyesuaian.index', [
            'bahan' => Bahan::orderBy('nama')->get(),
            'riwayat' => StokPenyesuaian::with(['bahan', 'user'])
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bahan_id' => ['required', 'exists:bahan,id'],
            'selisih' => ['required', 'numeric', 'not_in:0'],
            'alasan' => ['required', 'string', 'max:255'],
        ]);

        // Baca stok dengan kunci baris, supaya dua penyesuaian bersamaan tidak saling menimpa.
        DB::transaction(function () use ($data, $request) {
            $bahan = Bahan::lockForUpdate()->findOrFail($data['bahan_id']);
            $stokBaru = (float) $bahan->stok + (float) $data['selisih'];

            // Stok tidak boleh minus. Penyesuaian yang membuatnya minus ditolak.
            if ($stokBaru < 0) {
                throw ValidationException::withMessages([
                    'selisih' => 'Stok tidak boleh minus. Stok sekarang ' . $bahan->stok . ' ' . $bahan->satuan . '.',
                ]);
            }

            StokPenyesuaian::create([
                'bahan_id' => $bahan->id,
                'selisih' => $data['selisih'],
                'alasan' => $data['alasan'],
                'user_id' => $request->user()->id,
            ]);

            $bahan->update(['stok' => $stokBaru]);
        });

        return redirect('/stok-penyesuaian')->with('status', 'Penyesuaian stok dicatat.');
    }
}