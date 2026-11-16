@extends('layouts.kasir')

@section('judul', 'Daftar pesanan')

@section('isi')
@php
    $warna = [
        'Baru' => 'bg-[#efe3c2] text-[#b5452f]',
        'Diproses' => 'bg-[#fbe9b7] text-[#8A6100]',
        'Selesai' => 'bg-[#e3efe6] text-[#2A6B45]',
        'Dibatalkan' => 'bg-[#e6dcc0] text-[#6f6a5c]',
    ];
    $nomor = fn ($p) => '#A-' . str_pad($p->id, 3, '0', STR_PAD_LEFT);
@endphp

@if (session('status'))
    <div class="mb-4 rounded-xl bg-[#e9eee0] px-5 py-3 text-sm font-bold text-[#2A6B45]">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-xl bg-[#f9d5cc] px-5 py-3 text-sm font-bold text-[#8E2A20]">
        @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
    </div>
@endif

<div class="flex items-start gap-6">

    {{-- Kiri: filter + tabel --}}
    <div class="min-w-0 flex-1">
        <div class="mb-4 flex gap-2">
            @foreach ([null => 'Semua', 'Baru' => 'Baru', 'Diproses' => 'Diproses', 'Selesai' => 'Selesai'] as $nilai => $label)
                <a href="/pesanan{{ $nilai ? '?status=' . $nilai : '' }}"
                   class="rounded-full border border-[#cdbf9f] px-5 py-2 text-sm font-bold {{ ($status ?? null) === $nilai ? 'bg-[#b5452f] text-[#fffdf7]' : 'bg-[#fffdf7]' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] shadow-[0_3px_0_#cdbf9f]">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-[#f7f2e6] text-sm font-bold text-[#6f6a5c]">
                        <th class="px-5 py-3">No. Pesanan</th>
                        <th class="py-3">Waktu</th>
                        <th class="py-3">Metode</th>
                        <th class="py-3">Total</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesanan as $p)
                        <tr class="border-t border-[#e6dcc0] {{ $terpilih && $terpilih->id === $p->id ? 'bg-[#fbf6e8]' : '' }}">
                            <td class="px-5 py-4 font-medium">
                                <a href="/pesanan?{{ $status ? 'status=' . $status . '&' : '' }}pilih={{ $p->id }}" class="hover:text-[#b5452f]">{{ $nomor($p) }}</a>
                            </td>
                            <td>{{ $p->created_at->format('H:i') }}</td>
                            <td>{{ $p->metode_bayar }}</td>
                            <td>Rp{{ number_format($p->total, 0, ',', '.') }}</td>
                            <td><span class="rounded-full px-3 py-1 text-[13px] font-bold {{ $warna[$p->status] ?? '' }}">{{ $p->status }}</span></td>
                            <td>
                                <div class="flex gap-2">
                                    @if ($p->status === 'Baru')
                                        <form method="POST" action="/pesanan/{{ $p->id }}/status">@csrf
                                            <button class="rounded-full bg-[#b5452f] px-4 py-1 text-sm font-bold text-[#fffdf7] hover:brightness-110">Proses</button>
                                        </form>
                                        <form method="POST" action="/pesanan/{{ $p->id }}/batal">@csrf
                                            <button class="rounded-full border-[1.5px] border-[#b5452f] px-4 py-1 text-sm font-bold text-[#b5452f] hover:bg-[#b5452f] hover:text-[#fffdf7]">Batalkan</button>
                                        </form>
                                    @elseif ($p->status === 'Diproses')
                                        <form method="POST" action="/pesanan/{{ $p->id }}/status">@csrf
                                            <button class="rounded-full border-[1.5px] border-[#b5452f] px-4 py-1 text-sm font-bold text-[#b5452f] hover:bg-[#b5452f] hover:text-[#fffdf7]">Selesai</button>
                                        </form>
                                    @else
                                        <span class="text-[#6f6a5c]">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-[#6f6a5c]">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kanan: detail pesanan terpilih --}}
    @if ($terpilih)
        <aside class="w-[290px] shrink-0 rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-5 shadow-[0_3px_0_#cdbf9f]">
            <h3 class="text-xl font-bold">Pesanan {{ $nomor($terpilih) }}</h3>
            <p class="text-sm font-bold text-[#6f6a5c]">{{ $terpilih->status }} · {{ $terpilih->metode_bayar }} · {{ $terpilih->created_at->format('H:i') }}</p>

            <div class="mt-4">
                @foreach ($terpilih->items as $item)
                    <div class="flex justify-between py-1 text-sm font-bold">
                        <span>{{ $item->qty }}× {{ $item->menu->nama }}</span>
                        <span>Rp{{ number_format($item->harga_satuan * $item->qty, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-[#e6dcc0] pt-3">
                <span class="font-bold">Total</span>
                <b class="text-xl text-[#b5452f]">Rp{{ number_format($terpilih->total, 0, ',', '.') }}</b>
            </div>

            <div class="mt-4 rounded-xl bg-[#f0e3c3] px-4 py-2">
                <p class="text-sm font-bold text-[#b5452f]">Status: {{ $terpilih->status }}</p>
                <p class="text-xs text-[#6f6a5c]">Batalkan hanya saat Baru</p>
            </div>

            <div class="mt-4 space-y-2">
                @if ($terpilih->status === 'Baru')
                    <form method="POST" action="/pesanan/{{ $terpilih->id }}/status">@csrf
                        <button class="h-11 w-full rounded-xl bg-[#b5452f] font-bold text-[#fffdf7] hover:brightness-110">Proses pesanan</button>
                    </form>
                    <form method="POST" action="/pesanan/{{ $terpilih->id }}/batal">@csrf
                        <button class="h-11 w-full rounded-xl border border-[#b5452f] font-bold text-[#b5452f] hover:bg-[#b5452f] hover:text-[#fffdf7]">Batalkan pesanan</button>
                    </form>
                @elseif ($terpilih->status === 'Diproses')
                    <form method="POST" action="/pesanan/{{ $terpilih->id }}/status">@csrf
                        <button class="h-11 w-full rounded-xl bg-[#b5452f] font-bold text-[#fffdf7] hover:brightness-110">Tandai selesai</button>
                    </form>
                @endif
                <a href="/pesanan/{{ $terpilih->id }}/struk" class="flex h-11 items-center justify-center rounded-xl bg-[#f0e3c3] font-bold text-[#b5452f]">Cetak struk</a>
            </div>
        </aside>
    @endif
</div>
@endsection