@extends('layouts.manajer')

@section('judul', 'Menu')

@section('isi')
<div class="flex items-start gap-8">

    {{-- Kiri: daftar menu --}}
    <section class="w-[310px] shrink-0 rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-2xl font-bold">Menu</h2>
            <a href="/menu/create" class="rounded-full bg-[#b5452f] px-4 py-1 text-sm font-bold text-[#fffdf7] hover:brightness-110">Tambah</a>
        </div>

        @foreach ($daftar as $m)
            <div class="flex items-start justify-between border-b border-[#e6dcc0] py-4">
                <div>
                    <a href="/menu/{{ $m->id }}"
                       class="text-lg font-bold {{ $terpilih->id === $m->id ? 'text-[#b5452f]' : '' }}">{{ $m->nama }}</a>
                    @if ($m->resep_items_count === 0)
                        <p class="mt-1">
                            <span class="rounded-full bg-[#f9d5cc] px-3 py-1 text-sm font-bold text-[#8E2A20]">Belum ada resep</span>
                        </p>
                    @endif
                </div>

                {{-- Saklar aktif / nonaktif --}}
                <form method="POST" action="/menu/{{ $m->id }}/aktif">
                    @csrf
                    @method('PATCH')
                    <button type="submit" title="{{ $m->aktif ? 'Aktif' : 'Nonaktif' }}"
                            class="relative h-6 w-[38px] rounded-full {{ $m->aktif ? 'bg-[#b5452f]' : 'bg-[#e6dcc0]' }}">
                        <span class="absolute top-[3px] h-[18px] w-[18px] rounded-full bg-[#fffdf7] {{ $m->aktif ? 'right-[3px]' : 'left-[3px]' }}"></span>
                    </button>
                </form>
            </div>
        @endforeach
    </section>

    {{-- Kanan: detail menu --}}
    <div class="min-w-0 flex-1">
        <section class="rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-6 shadow-[0_3px_0_#cdbf9f]">
            <h2 class="text-xl font-bold">{{ $terpilih->exists ? 'Detail menu: ' . $terpilih->nama : 'Tambah menu' }}</h2>

            @if ($errors->any())
                <div class="mt-3 rounded-xl bg-[#f9d5cc] px-4 py-2 text-sm font-bold text-[#8E2A20]">
                    @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
                </div>
            @endif

            <form method="POST" action="{{ $terpilih->exists ? '/menu/' . $terpilih->id : '/menu' }}"
                  class="mt-3 grid grid-cols-2 gap-x-6 gap-y-4">
                @csrf
                @if ($terpilih->exists) @method('PUT') @endif

                <div>
                    <label class="text-sm font-bold text-[#6f6a5c]">Nama menu</label>
                    <input type="text" name="nama" value="{{ old('nama', $terpilih->nama) }}"
                           class="mt-1 h-11 w-full rounded-xl border border-[#cdbf9f] px-4 outline-none focus:border-[#b5452f]">
                </div>
                <div>
                    <label class="text-sm font-bold text-[#6f6a5c]">Harga jual (Rp)</label>
                    <input type="number" name="harga" min="1" value="{{ old('harga', $terpilih->harga) }}"
                           class="mt-1 h-11 w-full rounded-xl border border-[#cdbf9f] px-4 outline-none focus:border-[#b5452f]">
                </div>
                <div>
                    <label class="text-sm font-bold text-[#6f6a5c]">Kategori</label>
                    <select name="kategori"
                            class="mt-1 h-11 w-full rounded-xl border border-[#cdbf9f] bg-[#fffdf7] px-4 outline-none focus:border-[#b5452f]">
                        <option value="">Pilih kategori</option>
                        @foreach ($kategori as $k)
                            <option value="{{ $k }}" @selected(old('kategori', $terpilih->kategori) === $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-[#b5452f] font-bold text-[#fffdf7] hover:brightness-110">Simpan</button>

                    @if ($terpilih->exists)
                        {{-- Tombol ini milik form #formAktif di bawah (atribut form=). --}}
                        <button type="submit" form="formAktif"
                                class="h-11 flex-1 rounded-xl border border-[#b5452f] font-bold text-[#b5452f] hover:bg-[#b5452f] hover:text-[#fffdf7]">
                            {{ $terpilih->aktif ? 'Nonaktifkan menu' : 'Aktifkan menu' }}
                        </button>
                    @endif
                </div>
            </form>

            @if ($terpilih->exists)
                <form id="formAktif" method="POST" action="/menu/{{ $terpilih->id }}/aktif" class="hidden">
                    @csrf
                    @method('PATCH')
                </form>
            @endif
        </section>
    </div>
</div>
@endsection