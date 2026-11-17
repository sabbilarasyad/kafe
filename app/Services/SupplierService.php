<?php

namespace App\Services;

use App\Models\Bahan;
use Illuminate\Support\Facades\Http;

class SupplierService
{
    public function sinkronHarga(): bool
    {
        try {
            $respons = Http::withHeaders([
                'X-API-Key' => config('services.supplier.key'),
            ])->timeout(5)->get(config('services.supplier.url') . '/harga-bahan');

            // Supplier menjawab tapi menolak (mis. 401 key salah): harga lama tetap dipakai.
            if (! $respons->successful()) {
                return false;
            }

            $tanggal = $respons->json('diperbarui');
            $daftar = $respons->json('data');

            if (! is_array($daftar)) {
                return false;
            }

            foreach ($daftar as $item) {
                // 1. Cocokkan lewat kode (BHN-001 dst.).
                $bahan = null;
                if (! empty($item['kode'])) {
                    $bahan = Bahan::where('kode', $item['kode'])->first();
                }

                // 2. Cadangan: cocokkan lewat nama_kunci kalau kode tidak ada.
                if (! $bahan && ! empty($item['nama'])) {
                    $bahan = Bahan::where('nama_kunci', strtolower(trim($item['nama'])))->first();
                }

                // Bahan yang tidak dikenal dilewati, tidak dibuat baru.
                if (! $bahan) {
                    continue;
                }

                // Satuan harus sama. Kalau tidak, harga tidak boleh ditimpa
                // karena artinya berbeda (mis. per g vs per kg).
                if (($item['satuan_dasar'] ?? null) !== $bahan->satuan) {
                    continue;
                }

                $bahan->update([
                    'harga_per_satuan' => $item['harga_per_satuan'],
                    'harga_diperbarui' => $tanggal,
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            // Internet mati, timeout, atau format tak terduga: jangan crash.
            // Harga dan tanggal lama dibiarkan, itulah fallback-nya.
            return false;
        }
    }
}