@extends('layouts.kasir')

@section('judul', 'Buat pesanan')

@section('isi')
@php
    // Data menu untuk JavaScript: id => nama, harga, kategori.
    $dataMenu = $menu->mapWithKeys(fn ($m) => [$m->id => ['nama' => $m->nama, 'harga' => $m->harga, 'kategori' => $m->kategori]]);
@endphp

<div class="flex items-start gap-6">

    {{-- Kiri: menu --}}
    <div class="min-w-0 flex-1">
        <h2 class="mb-3 text-[28px] font-bold">Menu</h2>

        <div class="mb-4 flex flex-wrap gap-2">
            <button type="button" data-kat="" class="kat-chip rounded-full border border-[#cdbf9f] px-5 py-2 text-sm font-bold" style="background:#b5452f;color:#fffdf7">Semua {{ $menu->count() }}</button>
            @foreach ($menu->groupBy('kategori') as $kat => $isi)
                <button type="button" data-kat="{{ $kat }}" class="kat-chip rounded-full border border-[#cdbf9f] bg-[#fffdf7] px-5 py-2 text-sm font-bold">{{ $kat }} {{ $isi->count() }}</button>
            @endforeach
        </div>

        <div class="grid grid-cols-3 gap-4">
            @foreach ($menu as $m)
                @php
                    $sisa = $m->porsiTersedia();
                    $tanpaResep = $m->resepItems->isEmpty();
                @endphp

                <div data-kartu="{{ $m->kategori }}" class="rounded-[18px] border border-[#cdbf9f] bg-[#fffdf7] p-2 shadow-[0_3px_0_#cdbf9f]">
                    <div class="relative h-[108px] rounded-xl" style="background:linear-gradient(135deg,#fbe9b7,#f2c75c)">
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
                        <p class="mt-1 text-sm text-[#6f6a5c]">{{ $tanpaResep ? 'Belum ada resep' : 'Sisa ' . $sisa . ' porsi' }}</p>

                                                @if ($sisa > 0)
                            @if ($m->kategori === 'Minuman')
                                <div class="mt-1 flex gap-1">
                                    <button type="button" onclick="tambah({{ $m->id }}, 'Reguler')"
                                            class="flex-1 rounded-full bg-[#b5452f] py-[2px] text-sm font-bold text-[#fffdf7] hover:brightness-110">+ Reguler</button>
                                    <button type="button" onclick="tambah({{ $m->id }}, 'Large')"
                                            class="flex-1 rounded-full border-2 border-[#b5452f] py-[2px] text-sm font-bold text-[#b5452f] hover:bg-[#fdeee9]">+ Large</button>
                                </div>
                            @else
                                <button type="button" onclick="tambah({{ $m->id }}, 'Reguler')"
                                        class="mt-1 w-full rounded-full bg-[#b5452f] py-[2px] text-sm font-bold text-[#fffdf7] hover:brightness-110">+ Tambah</button>
                            @endif
                        @else
                            <p class="mt-1 rounded-full bg-[#e6dcc0] py-[2px] text-center text-sm font-bold text-[#6f6a5c]">Tidak tersedia</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Kanan: keranjang --}}
    <form method="POST" action="/pesanan" class="sticky top-4 w-[340px] shrink-0 rounded-[22px] border border-[#cdbf9f] bg-[#fffdf7] p-5 shadow-[0_3px_0_#cdbf9f]">
        @csrf

        <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold">Keranjang</h3>
            <span id="jumlah-item" class="rounded-full bg-[#efe3c2] px-3 py-1 text-xs font-bold text-[#b5452f]">0 item</span>
        </div>

        <div id="daftar-keranjang" class="mt-3"></div>
        <div id="input-item"></div>

        @if ($errors->any())
            <div class="mt-3 rounded-xl border border-[#b5452f] bg-[#fdeee9] px-4 py-3 text-sm text-[#8E2A20]">
                @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <div class="mt-4 rounded-2xl bg-[#f7f2e6] px-4 py-3 text-sm text-[#6f6a5c]">
            <div class="flex justify-between"><span>Subtotal</span><b id="t-subtotal" class="text-[#2b2a26]">Rp0</b></div>
            <div class="mt-1 flex justify-between"><span>Service charge 5%</span><b id="t-service" class="text-[#2b2a26]">Rp0</b></div>
            <div class="mt-1 flex justify-between"><span>Pajak 10%</span><b id="t-pajak" class="text-[#2b2a26]">Rp0</b></div>
            <div class="mt-3 flex items-center justify-between border-t border-[#cdbf9f] pt-3">
                <span class="text-base font-bold text-[#2b2a26]">Total</span>
                <b id="t-total" class="text-[28px] text-[#b5452f]">Rp0</b>
            </div>
        </div>

        <p class="mt-5 text-xs font-bold tracking-wide text-[#6f6a5c]">METODE BAYAR</p>
        <div class="mt-2 grid grid-cols-2 gap-3">
            <label>
                <input type="radio" name="metode_bayar" value="Tunai" class="peer sr-only" @checked(old('metode_bayar', 'Tunai') === 'Tunai')>
                <span class="block cursor-pointer rounded-xl border border-[#cdbf9f] py-2 text-center font-bold peer-checked:bg-[#b5452f] peer-checked:text-[#fffdf7]">Tunai</span>
            </label>
            <label>
                <input type="radio" name="metode_bayar" value="QRIS" class="peer sr-only" @checked(old('metode_bayar') === 'QRIS')>
                <span class="block cursor-pointer rounded-xl border border-[#cdbf9f] py-2 text-center font-bold peer-checked:bg-[#b5452f] peer-checked:text-[#fffdf7]">QRIS</span>
            </label>
        </div>

        <button id="tombol" type="submit" disabled
                class="mt-5 h-[50px] w-full rounded-xl bg-[#b5452f] text-base font-bold text-[#fffdf7] hover:brightness-110 disabled:cursor-not-allowed disabled:bg-[#e6dcc0] disabled:text-[#6f6a5c]">
            Konfirmasi pesanan
        </button>
        <p class="mt-2 text-center text-xs text-[#6f6a5c]">Stok dipotong saat pesanan dikonfirmasi.</p>
    </form>
</div>

<script>
    const MENU = @json($dataMenu);
    // Kunci keranjang: "id|ukuran", contoh "3|Reguler" atau "3|Large".
    const keranjang = {};
    let kategoriAktif = '';

    // Hanya Minuman yang punya pilihan Large.
    function bisaLarge(id) {
        return MENU[id].kategori === 'Minuman';
    }

    function tambah(id, ukuran = 'Reguler') {
        const kunci = id + '|' + ukuran;
        keranjang[kunci] = (keranjang[kunci] || 0) + 1;
        gambar();
    }

    function ubah(kunci, selisih) {
        keranjang[kunci] += selisih;
        if (keranjang[kunci] <= 0) delete keranjang[kunci];
        gambar();
    }

    function rupiah(angka) {
        return 'Rp' + Math.round(angka).toLocaleString('id-ID');
    }

    function esc(teks) {
        return String(teks).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Harga satu porsi sesuai ukuran (Large = harga + 5.000). Server tetap yang menghitung angka final.
    function hargaSatu(id, ukuran) {
        return MENU[id].harga + (ukuran === 'Large' ? 5000 : 0);
    }

    function gambar() {
        const daftar = document.getElementById('daftar-keranjang');
        const input = document.getElementById('input-item');
        daftar.innerHTML = '';
        input.innerHTML = '';

        let subtotal = 0, jumlahItem = 0, urut = 0;

        for (const kunci in keranjang) {
            const [id, ukuran] = kunci.split('|');
            const qty = keranjang[kunci];
            const m = MENU[id];
            subtotal += hargaSatu(id, ukuran) * qty;
            jumlahItem += qty;

            daftar.insertAdjacentHTML('beforeend', `
                <div class="flex items-center justify-between border-b border-[#e6dcc0] py-3">
                    <div>
                        <p class="font-bold">${esc(m.nama)} ${ukuran === 'Large' ? '<span class="text-xs text-[#b5452f]">(Large)</span>' : ''}</p>
                        <p class="text-sm text-[#6f6a5c]">${rupiah(hargaSatu(id, ukuran))} / porsi</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="ubah('${kunci}', -1)" class="px-1 text-[#b5452f]">−</button>
                        <span>${qty}</span>
                        <button type="button" onclick="ubah('${kunci}', 1)" class="px-1 text-[#b5452f]">+</button>
                    </div>
                </div>`);

            // Kirim sebagai field tersembunyi untuk server.
            input.insertAdjacentHTML('beforeend',
                `<input type="hidden" name="items[${urut}][menu_id]" value="${id}">` +
                `<input type="hidden" name="items[${urut}][qty]" value="${qty}">` +
                `<input type="hidden" name="items[${urut}][ukuran]" value="${ukuran}">`);
            urut++;
        }

        const sv = Math.round(subtotal * 0.05);
        const px = Math.round((subtotal + sv) * 0.10);

        document.getElementById('jumlah-item').textContent = jumlahItem + ' item';
        document.getElementById('t-subtotal').textContent = rupiah(subtotal);
        document.getElementById('t-service').textContent = rupiah(sv);
        document.getElementById('t-pajak').textContent = rupiah(px);
        document.getElementById('t-total').textContent = rupiah(subtotal + sv + px);
        document.getElementById('tombol').disabled = jumlahItem === 0;

        if (jumlahItem === 0) {
            daftar.innerHTML = '<p class="py-6 text-center text-sm text-[#6f6a5c]">Belum ada item. Pilih menu di kiri.</p>';
        }
    }

    // Filter kategori.
    document.querySelectorAll('.kat-chip').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const kat = btn.dataset.kat;
            document.querySelectorAll('[data-kartu]').forEach(function (kartu) {
                kartu.style.display = (kat === '' || kartu.dataset.kartu === kat) ? '' : 'none';
            });
            document.querySelectorAll('.kat-chip').forEach(function (b) {
                const aktif = b.dataset.kat === kat;
                b.style.background = aktif ? '#b5452f' : '#fffdf7';
                b.style.color = aktif ? '#fffdf7' : '';
            });
        });
    });

    gambar();
</script>
@endsection