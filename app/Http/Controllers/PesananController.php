<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PesananController extends Controller
{
    // Halaman Kasir membuat pesanan: hanya menu yang aktif.
    public function create()
    {
        $menu = Menu::with('resepItems.bahan')
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        return view('pesanan.create', compact('menu'));
    }

    // Kasir menekan "Konfirmasi pesanan". Aturannya sama dengan API (PesananService).
    // Tugas: Memeriksa input Kasir dan meneruskannya ke layanan pembuat pesanan.
    // Kenapa: Aturan pesanan yang sama dipakai Kasir dan API supaya hasilnya seragam.
    // Contoh: Jumlah 2 diterima, tetapi jumlah 0 ditolak.
    public function store(Request $request, PesananService $layanan)
    {
        $data = $request->validate([
            'metode_bayar' => ['required', Rule::in(['Tunai', 'QRIS'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:menu,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.ukuran' => ['nullable', Rule::in(['Reguler', 'Large'])],
        ]);

        $pesanan = $layanan->buat($request->user(), $data['items'], $data['metode_bayar']);

        return redirect('/pesanan/' . $pesanan->id . '/struk')->with('baru', true);
    }

    public function struk(Pesanan $pesanan)
    {
        $pesanan->load('items.menu');

        return view('pesanan.struk', compact('pesanan'));
    }

    // Daftar pesanan + detail satu pesanan di kanan.
    public function index(Request $request)
    {
        $status = $request->query('status');

        $pesanan = Pesanan::with('items.menu')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        // Pesanan yang sedang dilihat: ?pilih=ID, atau yang paling baru.
        $terpilih = $pesanan->firstWhere('id', (int) $request->query('pilih')) ?? $pesanan->first();

        return view('pesanan.index', compact('pesanan', 'terpilih', 'status'));
    }

    // Naikkan status satu tingkat: Baru -> Diproses -> Selesai.
    // Tugas: Memindahkan pesanan ke status berikutnya yang diizinkan.
    // Kenapa: Server menentukan urutan status agar perubahan tidak melompati tahap.
    // Contoh: Pesanan Baru berubah menjadi Diproses.
    public function ubahStatus(Pesanan $pesanan)
    {
        $urutan = ['Baru' => 'Diproses', 'Diproses' => 'Selesai'];

        // Server yang menentukan status berikutnya, bukan browser.
        $berikutnya = $urutan[$pesanan->status] ?? null;

        if ($berikutnya === null) {
            return back()->withErrors(['status' => 'Status pesanan ini tidak diubah lagi.']);
        }

        $pesanan->update(['status' => $berikutnya]);

        return back()->with('status', 'Pesanan sekarang berstatus ' . $berikutnya . '.');
    }

    // Batalkan pesanan (hanya saat Baru) dan kembalikan stok persis dari catatan pesanan_bahan.
    // Tugas: Membatalkan pesanan baru dan mengembalikan bahan yang sudah dipotong.
    // Kenapa: Catatan bahan pesanan membuat jumlah yang dikembalikan sesuai pemakaian awal.
    // Contoh: Pesanan memakai 30 g, pembatalan menambah stok kembali 30 g.
    public function batal(Pesanan $pesanan)
    {
        if ($pesanan->status !== 'Baru') {
            return back()->withErrors(['status' => 'Pesanan hanya bisa dibatalkan saat berstatus Baru.']);
        }

        DB::transaction(function () use ($pesanan) {
            foreach ($pesanan->bahanTerpotong as $baris) {
                Bahan::findOrFail($baris->bahan_id)->increment('stok', $baris->jumlah);
            }

            $pesanan->update(['status' => 'Dibatalkan']);
        });

        return back()->with('status', 'Pesanan dibatalkan, stok dikembalikan.');
    }
}