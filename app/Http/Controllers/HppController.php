<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\Menu;
use App\Services\SupplierService;

class HppController extends Controller
{
    // Tugas: Menyiapkan data harga pokok produksi dan margin untuk halaman laporan.
    // Kenapa: Menu yang datanya belum lengkap dilewati agar angka laporan tidak menyesatkan.
    // Contoh: Harga jual Rp30.000 dan HPP Rp12.000 memberi margin Rp18.000.
    public function index(SupplierService $supplier)
    {
        // Coba ambil harga terbaru. false = Supplier gagal, harga lama dipakai.
        $berhasil = $supplier->sinkronHarga();

        // Tanggal pembaruan harga terakhir yang tersimpan.
        $diperbarui = Bahan::max('harga_diperbarui');

        $baris = [];
        $dilewati = [];

        foreach (Menu::with('resepItems.bahan')->orderBy('nama')->get() as $menu) {
            // HPP dihitung di Menu::hpp(). Null = belum ada resep atau ada bahan tanpa harga.
            $hpp = $menu->hpp();

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
}