@extends('layouts.manajer')

@section('judul', 'Kelola bahan')

@section('isi')
<div class="mb-2 flex justify-end">
    <a href="/bahan/create" class="rounded-full bg-[#b5452f] px-5 py-1 text-base font-bold text-[#fffdf7] hover:brightness-110">Tambah bahan</a>
</div>

<div class="overflow-hidden rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] shadow-[0_3px_0_#cdbf9f]">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-[#f7f2e6] text-sm font-bold text-[#6f6a5c]">
                <th class="px-5 py-3">Nama bahan</th>
                <th class="py-3">Satuan dasar</th>
                <th class="py-3">Stok</th>
                <th class="py-3">Batas minimum</th>
                <th class="py-3">Harga / satuan</th>
                <th class="py-3">Status</th>
                <th class="py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bahan as $b)
                <tr class="border-t border-[#e6dcc0] text-base">
                    <td class="px-5 py-5">{{ $b->nama }}</td>
                    <td>{{ $b->satuan }}</td>
                    <td>{{ rtrim(rtrim(number_format($b->stok, 2, ',', '.'), '0'), ',') }}</td>
                    <td>{{ rtrim(rtrim(number_format($b->batas_minimum, 2, ',', '.'), '0'), ',') }} {{ $b->satuan }}</td>
                    <td>
                        @if ($b->harga_per_satuan !== null)
                            Rp{{ rtrim(rtrim(number_format($b->harga_per_satuan, 2, ',', '.'), '0'), ',') }} / {{ $b->satuan }}
                        @else
                            <span class="text-[#6f6a5c]">Belum ada</span>
                        @endif
                    </td>
                    <td>
                        {{-- Aturan "menipis" dari soal: stok sama dengan atau kurang dari batas minimum. --}}
                        @if ($b->stok <= $b->batas_minimum)
                            <span class="rounded-full bg-[#fbe9b7] px-3 py-1 text-sm font-bold text-[#8A6100]">Menipis</span>
                        @else
                            <span class="rounded-full bg-[#e3efe6] px-3 py-1 text-[13px] font-bold text-[#2A6B45]">Aman</span>
                        @endif
                    </td>
                    <td>
                        <a href="/bahan/{{ $b->id }}/edit" class="rounded-full border-[1.5px] border-[#b5452f] px-4 py-1 text-sm font-bold text-[#b5452f] hover:bg-[#b5452f] hover:text-[#fffdf7]">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-[#6f6a5c]">Belum ada bahan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6 rounded-2xl bg-[#f0e3c3] px-6 py-4">
    <p class="font-bold">Nama tidak boleh ganda (huruf besar/kecil dan spasi diabaikan).</p>
    <p class="mt-1 text-sm text-[#6f6a5c]">Batas minimum awal setiap bahan = 0. Stok di bawah batas ditandai menipis.</p>
</div>
@endsection