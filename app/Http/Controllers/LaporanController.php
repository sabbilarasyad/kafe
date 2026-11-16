<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\Menu;
use App\Models\PesananItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        // Periode bawaan: awal bulan sampai hari ini.
        $dari = Carbon::parse($request->query('dari', now()->startOfMonth()->toDateString()));
        $sampai = Carbon::parse($request->query('sampai', now()->toDateString()));

        // 1. Penjualan per menu dalam periode, Pesanan yang dibatalkan tidak dihitung.
        //    Omzet memakai harga yang tersimpan saat pesanan dibuat, bukan harga menu sekarang.
        $penjualan = PesananItem::query()
            ->join('pesanan', 'pesanan.id', '=', 'pesanan_item.pesanan_id')
            ->join('menu', 'menu.id', '=', 'pesanan_item.menu_id')
            ->where('pesanan.status', '!=', 'Dibatalkan')
            ->whereBetween('pesanan.created_at', [$dari->copy()->startOfDay(), $sampai->copy()->endOfDay()])
            ->groupBy('menu.id', 'menu.nama')
            ->selectRaw('menu.nama as nama, SUM(pesanan_item.qty) as qty, SUM(pesanan_item.qty * pesanan_item.harga_satuan) as omzet')
            ->orderByDesc('qty')
            ->get();

        // 2. Bahan menipis: stok sama dengan atau kurang dari batas minimum.
        $menipis = Bahan::whereColumn('stok', '<=', 'batas_minimum')
            ->orderBy('nama')
            ->get();

        // 3. Margin per menu (menu yang HPP-nya belum bisa dihitung dilewati).
        $margin = [];
        foreach (Menu::with('resepItems.bahan')->orderBy('nama')->get() as $menu) {
            $hpp = $menu->hpp();

            if ($hpp === null) {
                continue;
            }

            $margin[] = [
                'nama' => $menu->nama,
                'harga' => $menu->harga,
                'margin' => $menu->harga - $hpp,
            ];
        }

        return view('laporan.index', compact('dari', 'sampai', 'penjualan', 'menipis', 'margin'));
    }
}
