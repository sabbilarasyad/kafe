@extends('layouts.kasir')

@section('judul', 'Buat pesanan')

@section('isi')
<h2 class="mb-4 text-[28px] font-bold">Menu</h2>

<div class="grid grid-cols-4 gap-4">
    @foreach ($menu as $m)
        @php
            $sisa = $m->porsiTersedia();
            $tanpaResep = $m->resepItems->isEmpty();
        @endphp

        <div class="rounded-[18px] border border-[#cdbf9f] bg-[#fffdf7] p-2 shadow-[0_3px_0_#cdbf9f]">
            <div class="relative h-[108px] rounded-xl"
                 style="background:linear-gradient(135deg,#fbe9b7,#f2c75c)">
                @if ($sisa === 0)
                    <span class="absolute left-2 top-2 rounded-full bg-[#b5452f] px-3 py-[2px] text-xs font-bold text-[#fffdf7]">Habis</span>
                @elseif ($sisa <= 5)
                    <span class="absolute left-2 top-2 rounded-full bg-[#e0a43a] px-3 py-[2px] text-xs font-bold text-[#fffdf7]">Sisa {{ $sisa }}</span>
                @endif
            </div>

            <div class="px-2 pb-1 pt-3">
                <p class="text-[11px] font-bold uppercase tracking-wide text-[#6f6a5c]">{{ $m->kategori }}</p>
                <p class="font-bold">{{ $m->nama }}</p>
                <p class="text-sm font-bold text-[#b5452f]">Rp{{ number_format($m->harga, 0, ',', '.') }}</p>

                <div class="mt-2 h-[5px] rounded-full bg-[#e6dcc0]">
                    <div class="h-[5px] rounded-full bg-[#1f4d45]" style="width: {{ min(100, $sisa * 5) }}%"></div>
                </div>
                <p class="mt-1 text-sm text-[#6f6a5c]">
                    {{ $tanpaResep ? 'Belum ada resep' : 'Sisa ' . $sisa . ' porsi' }}
                </p>

                @if ($sisa > 0)
                    <button type="button" class="mt-1 w-full rounded-full bg-[#b5452f] py-[2px] text-sm font-bold text-[#fffdf7] hover:brightness-110">+ Tambah</button>
                @else
                    <p class="mt-1 rounded-full bg-[#e6dcc0] py-[2px] text-center text-sm font-bold text-[#6f6a5c]">Tidak tersedia</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection