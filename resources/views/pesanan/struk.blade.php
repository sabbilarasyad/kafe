@extends('layouts.kasir')

@section('judul', 'Struk pesanan')

@section('isi')
<div class="w-[482px]">

    {{-- Banner hanya muncul tepat setelah pesanan dibuat --}}
    @if (session('baru'))
        <div class="rounded-t-[22px] border-t-4 border-[#1f4d45] bg-[#f0e3c3] px-12 pb-16 pt-10">
            <div class="flex items-center gap-4">
                <div class="flex h-[62px] w-[62px] items-center justify-center rounded-full bg-[#1f4d45] text-3xl text-[#fffdf7]">✓</div>
                <div>
                    <p class="text-xl font-bold">Pesanan {{ $pesanan->status }} tersimpan</p>
                    <p class="text-sm font-bold text-[#1f4d45]">Stok sudah dipotong sesuai resep.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="{{ session('baru') ? '-mt-8' : '' }} rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-7 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-center text-2xl font-bold">Kedai Kopi Senja</h2>
        <p class="mt-1 text-center text-sm text-[#6f6a5c]">
            #A-{{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}
            · {{ $pesanan->created_at->format('d M Y H:i') }}
            · {{ $pesanan->metode_bayar }}
            @if ($pesanan->nomor_meja) · Meja {{ $pesanan->nomor_meja }} @endif
        </p>

        <hr class="my-4 border-[#e6dcc0]">

        @foreach ($pesanan->items as $item)
            <div class="flex justify-between py-1 font-bold">
                <span>{{ $item->qty }}× {{ $item->menu->nama }}@if ($item->ukuran === 'Large') <span class="text-xs text-[#b5452f]">(Large)</span>@endif</span>
                {{-- Harga satuan yang tersimpan saat pesanan dibuat, bukan harga menu sekarang. --}}
                <span>Rp{{ number_format($item->harga_satuan * $item->qty, 0, ',', '.') }}</span>
            </div>
        @endforeach

        <hr class="my-4 border-[#e6dcc0]">

        <div class="space-y-1 text-[#6f6a5c]">
            <div class="flex justify-between"><span>Subtotal</span><b class="text-[#2b2a26]">Rp{{ number_format($pesanan->subtotal, 0, ',', '.') }}</b></div>
            <div class="flex justify-between"><span>Service charge 5%</span><b class="text-[#2b2a26]">Rp{{ number_format($pesanan->service, 0, ',', '.') }}</b></div>
            <div class="flex justify-between"><span>Pajak 10%</span><b class="text-[#2b2a26]">Rp{{ number_format($pesanan->pajak, 0, ',', '.') }}</b></div>
        </div>

        <hr class="my-4 border-[#e6dcc0]">

        <div class="flex items-center justify-between">
            <span class="text-lg font-bold">Total</span>
            <b class="text-[28px] text-[#b5452f]">Rp{{ number_format($pesanan->total, 0, ',', '.') }}</b>
        </div>

        <div class="mt-6 flex gap-3">
            <a href="/pesanan/create"
               class="flex h-11 flex-1 items-center justify-center rounded-xl bg-[#b5452f] font-bold text-[#fffdf7] hover:brightness-110">Pesanan baru</a>
        </div>
    </div>

    <p class="mt-3 text-center text-sm text-[#6f6a5c]">Batalkan atau ubah status lewat Daftar pesanan.</p>
</div>
@endsection