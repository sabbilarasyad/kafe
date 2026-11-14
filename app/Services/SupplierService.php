<?php

namespace App\Services;

use App\Models\Bahan;
use Illuminate\Support\Facades\Http;

class SupplierService
{
    public function sinkronHarga(): bool
    {
        try {
            // Tanya harga ke Supplier. Alamat dan key dibaca dari .env.
            $respons = Http::withHeaders([
                'X-API-Key' => config('services.supplier.key'),
            ])->timeout(5)->get(config('services.supplier.url') . '/harga-bahan');

            // Supplier menjawab tapi menolak (mis. 401 key salah):
            // jangan ubah apa pun, harga lama tetap dipakai.
            if (! $respons->successful()) {
                return false;
            }

            // Tanggal pembaruan harga dari Supplier.
            $tanggal = $respons->json('diperbarui');

            foreach ($respons->json('data') as $item) {
                // Samakan bentuk nama supaya cocok dengan kolom nama_kunci.
                $kunci = strtolower(trim($item['nama']));

                // Cari bahan yang sama di database.
                $bahan = Bahan::where('nama_kunci', $kunci)->first();

                // Bahan yang tidak dikenal dilewati, tidak dibuat baru.
                if (! $bahan) {
                    continue;
                }

                // Simpan harga baru dan tanggalnya.
                $bahan->update([
                    'harga_per_satuan' => $item['harga_per_satuan'],
                    'harga_diperbarui' => $tanggal,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            // Internet mati atau timeout: jangan crash,
            // harga dan tanggal lama dibiarkan (ini fallback-nya).
            return false;
        }
    }
}