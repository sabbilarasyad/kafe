<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\Menu;
use App\Services\SupplierService;

class HppController extends Controller
{
    public function index(SupplierService $supplier)
    {
        // Coba ambil harga terbaru. false = Supplier gagal, harga lama dipakai.
        $berhasil = $supplier->sinkronHarga();

        // Tanggal pembaruan harga terakhir yang tersimpan.
        $diperbarui = Bahan::max('harga_diperbarui');

        $baris = [];
        $dilewati = [];

        foreach (Menu::with('resepItems.bahan')->orderBy('nama')->get() as $menu) {
            $hpp = $this->hitung($menu);

            // Belum ada resep atau ada bahan tanpa harga: tidak bisa dihitung.
            if ($hpp === null) {
                $dilewati[] = $menu->nama;
                continue;
            }

            $margin = $menu->harga - $hpp;

            $baris[] = [
                'nama' => $menu->nama,
                'harga' => $menu->harga,
                'hpp' => $hpp,
                'margin' => $margin,
                'persen' => $menu->harga > 0 ? round($margin / $menu->harga * 100) : 0,
            ];
        }

        $jumlah = count($baris);
        $rata = $jumlah ? collect($baris)->avg('margin') : 0;
        $tertinggi = $jumlah ? collect($baris)->max('hpp') : 0;

        return view('hpp.index', compact('berhasil', 'diperbarui', 'baris', 'dilewati', 'jumlah', 'rata', 'tertinggi'));
    }

    // BAGIANMU: hitung HPP satu menu (lihat alur di bawah).
        // Hitung HPP satu menu. Hasilnya angka, atau null kalau tidak bisa dihitung.
    private function hitung(Menu $menu): ?float
    {
        // Menu tanpa resep: tidak bisa dihitung.
        if ($menu->resepItems->isEmpty()) {
            return null;
        }

        $total = 0;

        foreach ($menu->resepItems as $item) {
            // Bahan belum punya harga: HPP tidak bisa dihitung (bukan dianggap Rp0).
            if ($item->bahan->harga_per_satuan === null) {
                return null;
            }

            // Rumus soal: takaran x harga per satuan dasar, lalu dijumlahkan.
            $total += (float) $item->takaran * (float) $item->bahan->harga_per_satuan;
        }

        return $total;
    }
}
    