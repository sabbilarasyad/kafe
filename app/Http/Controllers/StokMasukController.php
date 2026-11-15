<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\StokMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokMasukController extends Controller
{
    // Form pembelian + riwayat terbaru.
    public function index()
    {
        return view('stok-masuk.index', [
            'bahan' => Bahan::orderBy('nama')->get(),
            'riwayat' => StokMasuk::with(['bahan', 'user'])
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'bahan_id' => ['required', 'exists:bahan,id'],
            'jumlah' => ['required', 'numeric', 'gt:0'],
        ]);

        // Dua langkah di bawah harus berhasil bersamaan, atau batal dua-duanya.
        DB::transaction(function () use ($data, $request) {
            // Catat pembelian.
            StokMasuk::create([
                'tanggal' => $data['tanggal'],
                'bahan_id' => $data['bahan_id'],
                'jumlah' => $data['jumlah'],
                'user_id' => $request->user()->id,
            ]);

            // Tambahkan stok bahan.
            Bahan::where('id', $data['bahan_id'])->increment('stok', $data['jumlah']);
        });

        return redirect('/stok-masuk')->with('status', 'Pembelian dicatat, stok bertambah.');
    }
}