@extends('layouts.manajer')

@section('judul', 'Impor data awal CSV')

@section('isi')
<div class="grid grid-cols-2 gap-6">

    {{-- Kiri: berkas dan tombol impor --}}
    <section class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-xl font-bold">Berkas data awal dari klien</h2>

        <div class="mt-4 space-y-3">
            @foreach ($berkas as $b)
                <div class="flex items-center justify-between rounded-2xl border border-[#cdbf9f] bg-[#f7f2e6] px-5 py-4">
                    <div>
                        <p class="text-lg font-bold">{{ $b['nama'] }}</p>
                        <p class="text-sm text-[#6f6a5c]">{{ $b['keterangan'] }}</p>
                    </div>
                    @if ($b['ada'])
                        <span class="rounded-full bg-[#e3efe6] px-3 py-1 text-sm font-bold text-[#2A6B45]">Ditemukan</span>
                    @else
                        <span class="rounded-full bg-[#f9d5cc] px-3 py-1 text-sm font-bold text-[#8E2A20]">Tidak ada</span>
                    @endif
                </div>
            @endforeach
        </div>

        <form method="POST" action="/impor" class="mt-5">
            @csrf
            <button type="submit" class="h-[50px] w-full rounded-xl font-bold text-[#fffdf7] hover:brightness-110"
                    style="background:linear-gradient(#c75a44,#b5452f)">Mulai impor</button>
        </form>

        <p class="mt-3 text-sm text-[#6f6a5c]">
            Berkas dibaca dari <b>database/data/awal</b>. Impor ulang aman (data tidak ganda),
            tetapi takaran resep kembali mengikuti CSV.
        </p>
    </section>

    {{-- Kanan: hasil pemeriksaan --}}
    <section class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-xl font-bold">Hasil pemeriksaan</h2>

        @if ($hasil)
            <table class="mt-4 w-full text-left">
                <thead>
                    <tr class="bg-[#f7f2e6] text-sm font-bold text-[#6f6a5c]">
                        <th class="px-3 py-2">Berkas</th>
                        <th class="py-2">Baris</th>
                        <th class="py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hasil as $h)
                        <tr class="border-t border-[#e6dcc0]">
                            <td class="px-3 py-3 font-medium">{{ $h['berkas'] }}</td>
                            <td>{{ $h['berhasil'] + $h['dilewati'] }}</td>
                            <td class="space-y-1 py-2">
                                <span class="inline-block rounded-full bg-[#e3efe6] px-3 py-1 text-[13px] font-bold text-[#2A6B45]">{{ $h['berhasil'] }} berhasil</span>
                                @if ($h['dicocokkan'] > 0)
                                    <span class="inline-block rounded-full bg-[#fbe9b7] px-3 py-1 text-[13px] font-bold text-[#8A6100]">{{ $h['dicocokkan'] }} salah ketik dicocokkan</span>
                                @endif
                                @if ($h['dilewati'] > 0)
                                    <span class="inline-block rounded-full bg-[#f9d5cc] px-3 py-1 text-[13px] font-bold text-[#8E2A20]">{{ $h['dilewati'] }} dilewati</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="mt-4 text-[#6f6a5c]">Belum ada impor yang dijalankan.</p>
        @endif
    </section>
</div>

{{-- Bawah: pencocokan nama bahan di resep --}}
@if ($pencocokan)
    <section class="mt-6 rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="text-xl font-bold">Pencocokan nama bahan di resep</h2>
        <table class="mt-4 w-full text-left">
            <thead>
                <tr class="bg-[#f7f2e6] text-sm font-bold text-[#6f6a5c]">
                    <th class="px-3 py-2">Nama di resep.csv</th>
                    <th class="py-2">Dicocokkan ke bahan</th>
                    <th class="py-2">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pencocokan as $p)
                    <tr class="border-t border-[#e6dcc0]">
                        <td class="px-3 py-3">{{ $p['nama'] }}</td>
                        <td>{{ $p['ke'] }}</td>
                        <td>
                            @if ($p['dilewati'])
                                <span class="rounded-full bg-[#f9d5cc] px-3 py-1 text-[13px] font-bold text-[#8E2A20]">{{ $p['ket'] }}</span>
                            @else
                                <span class="rounded-full bg-[#fbe9b7] px-3 py-1 text-[13px] font-bold text-[#8A6100]">{{ $p['ket'] }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endif
@endsection