@extends('layouts.manajer')

@section('judul', 'Resep')

@section('isi')
<div class="flex items-start gap-8">

    {{-- Kiri: pilih menu --}}
    <section class="w-[310px] shrink-0 rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <h2 class="mb-2 text-2xl font-bold">Pilih menu</h2>

        @foreach ($daftar as $m)
            <div class="border-b border-[#e6dcc0] py-4">
                <a href="/resep/{{ $m->id }}"
                   class="text-lg font-bold {{ $terpilih->id === $m->id ? 'text-[#b5452f]' : '' }}">{{ $m->nama }}</a>
                @if ($m->resep_items_count === 0)
                    <p class="mt-1">
                        <span class="rounded-full bg-[#f9d5cc] px-3 py-1 text-sm font-bold text-[#8E2A20]">Belum ada resep</span>
                    </p>
                @endif
            </div>
        @endforeach
    </section>

    {{-- Kanan: resep menu terpilih --}}
    <div class="min-w-0 flex-1">
        <section class="overflow-hidden rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] shadow-[0_3px_0_#cdbf9f]">
            <h2 class="px-6 pt-6 text-xl font-bold">Resep: {{ $terpilih->nama }}</h2>

            <table class="mt-3 w-full text-left">
                <thead>
                    <tr class="bg-[#f7f2e6] text-sm font-bold text-[#6f6a5c]">
                        <th class="px-6 py-3">Bahan</th>
                        <th class="py-3">Takaran</th>
                        <th class="py-3">Satuan</th>
                        <th class="py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($terpilih->resepItems as $item)
                        <tr class="border-t border-[#e6dcc0]">
                            <td class="px-6 py-3">{{ $item->bahan->nama }}</td>
                            <td>{{ rtrim(rtrim(number_format($item->takaran, 2, ',', '.'), '0'), ',') }}</td>
                            <td>{{ $item->bahan->satuan }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="/resep/item/{{ $item->id }}/edit"
                                       class="rounded-full border-[1.5px] border-[#b5452f] px-4 py-1 text-sm font-bold text-[#b5452f] hover:bg-[#b5452f] hover:text-[#fffdf7]">Edit</a>

                                    <form method="POST" action="/resep/item/{{ $item->id }}"
                                          onsubmit="return confirm('Hapus {{ $item->bahan->nama }} dari resep?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-full border-[1.5px] border-[#8E2A20] px-4 py-1 text-sm font-bold text-[#8E2A20] hover:bg-[#8E2A20] hover:text-[#fffdf7]">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-6 text-center text-[#6f6a5c]">Belum ada resep.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="border-t border-[#e6dcc0] p-6">
                <a href="/resep/{{ $terpilih->id }}/tambah"
                   class="inline-block rounded-xl bg-[#b5452f] px-6 py-3 font-bold text-[#fffdf7] hover:brightness-110">Tambah bahan</a>
            </div>
        </section>
    </div>
</div>
@endsection