<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    public function store(Request $request, PesananService $layanan)
    {
        $data = $request->validate([
            'nomor_meja' => ['required', 'integer', 'between:1,10'],
            'metode_bayar' => ['required', 'in:Tunai,QRIS'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menu,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        // Satu pelanggan hanya boleh punya satu pesanan berstatus Baru.
        $adaBaru = Pesanan::where('user_id', $request->user()->id)
            ->where('status', 'Baru')
            ->exists();

        if ($adaBaru) {
            throw ValidationException::withMessages([
                'pesanan' => 'Anda masih punya pesanan berstatus Baru. Tunggu sampai diproses.',
            ]);
        }

        // Aturan menu, stok, dan biaya semuanya ada di PesananService (sama dengan Kasir).
        $pesanan = $layanan->buat(
            $request->user(),
            $data['items'],
            $data['metode_bayar'],
            (int) $data['nomor_meja'],
        );

        return response()->json($this->bentuk($pesanan), 201);
    }

    // Bentuk satu pesanan untuk APK. Angkanya dibaca dari yang tersimpan, bukan dihitung ulang.
    private function bentuk(Pesanan $pesanan): array
    {
        $pesanan->load('items.menu');

        return [
            'id' => $pesanan->id,
            'nomor' => 'A-' . str_pad($pesanan->id, 3, '0', STR_PAD_LEFT),
            'status' => $pesanan->status,
            'nomor_meja' => $pesanan->nomor_meja,
            'metode_bayar' => $pesanan->metode_bayar,
            'dibuat' => $pesanan->created_at->toIso8601String(),
            'items' => $pesanan->items->map(fn ($i) => [
                'menu' => $i->menu->nama,
                'qty' => $i->qty,
                'harga_satuan' => (int) $i->harga_satuan,
                'subtotal' => (int) $i->harga_satuan * $i->qty,
            ])->values(),
            'subtotal' => (int) $pesanan->subtotal,
            'service' => (int) $pesanan->service,
            'pajak' => (int) $pesanan->pajak,
            'total' => (int) $pesanan->total,
        ];
    }
}

