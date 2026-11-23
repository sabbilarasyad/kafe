# Kedai Kopi Senja — Sistem Pemesanan dan Stok

UKK RPL 2026/2027, Soal 3 Varian C (Kafe).

## 1. Ringkasan

Sistem untuk Kedai Kopi Senja milik Kak Rani. Penggunanya tiga: **Kasir** dan **Manajer** memakai web desktop,
**Pelanggan** memakai aplikasi Android (APK). Masalah utama klien: pesanan ditulis di kertas, stok bahan baru
dihitung setelah kedai tutup, sehingga pelanggan bisa memesan menu yang bahannya sudah habis.

Alur yang dibangun: Manajer mengatur bahan, menu, resep, batas minimum, dan mencatat pembelian bahan. Saat pesanan
dibuat (oleh Kasir atau Pelanggan), sistem menghitung kebutuhan bahan dari **seluruh item**, menolak pesanan bila
bahan kurang (dan menampilkan bahan yang kurang), lalu memotong stok sesuai resep. Menu otomatis berstatus Habis
bila bahannya tidak cukup untuk satu porsi. Struk memuat subtotal, service 5%, pajak 10%, dan total dengan harga
yang dibekukan saat pesanan dibuat. Harga bahan diambil dari layanan Supplier untuk menghitung HPP dan margin;
bila layanan tidak terjangkau, harga terakhir yang tersimpan dipakai beserta tanggal pembaruannya.

Aturan pesanan hanya ditulis di satu tempat (`PesananService`), dipakai oleh web Kasir dan API Pelanggan, sehingga
aturan keduanya selalu sama.

Rancangan awal (use case, class diagram, activity diagram, skenario, wireframe) sudah diselaraskan dengan sistem yang
jadi di `docs/perancangan.md`; semua perbedaan dan alasannya dicatat di bagian 2 dokumen itu.

## 2. Teknologi

| Bagian | Teknologi | Alasan singkat |
|---|---|---|
| Server dan web | Laravel 13, PHP 8.5, Blade, Tailwind CSS (Vite) | Framework yang saya pelajari di sekolah; validasi, migrasi, dan autentikasi sudah tersedia. |
| Database | SQLite | Database relasional tanpa server tambahan, mudah dijalankan penguji dari repositori. |
| API | REST API Laravel dengan Sanctum (token) | APK mengambil semua data lewat API; hak akses dicek di server. |
| APK | Aplikasi hibrid Capacitor (HTML, CSS, JavaScript di dalam APK) | Seluruh tampilan ada di dalam APK dan datanya dari REST API (bukan WebView ke halaman web server). |

## 3. Cara menjalankan server

Dibuat untuk **Windows (PowerShell)**. Prasyarat: PHP 8.5 (ekstensi sqlite, curl, openssl, mbstring aktif),
Composer, Node.js 22.

1. Pasang dependensi (satu per satu):

   ```powershell
   composer install
   ```

   ```powershell
   npm install
   ```

2. Siapkan konfigurasi:

   ```powershell
   Copy-Item .env.example .env
   ```

   ```powershell
   php artisan key:generate
   ```

3. Isi `.env`:
   - `DB_CONNECTION=sqlite`
   - `APP_TIMEZONE=Asia/Jakarta`
   - `SUPPLIER_URL=https://ukksmkn1katapang.next-it.co.id/kafe`
   - `SUPPLIER_API_KEY=` isi dengan API key dari panitia (file `.env` tidak di-commit)

4. Buat file database kosong, lalu jalankan migrasi:

   ```powershell
   New-Item database\database.sqlite
   ```

   ```powershell
   php artisan migrate
   ```

5. Isi data. Urutan ini penting (impor harus sebelum seeder demo):

   ```powershell
   php artisan db:seed --class=StafSeeder
   ```

   ```powershell
   php artisan data:impor-awal
   ```

   ```powershell
   php artisan db:seed --class=DemoSeeder
   ```

6. Bangun aset tampilan, lalu jalankan server (`--host=0.0.0.0` agar HP bisa menjangkau server):

   ```powershell
   npm run build
   ```

   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```

Catatan:
- Di Windows, PHP memerlukan berkas `cacert.pem` (`curl.cainfo` dan `openssl.cafile` di `php.ini`) agar bisa
  memanggil layanan Supplier lewat HTTPS.
- Bila layanan Supplier tidak terjangkau karena DNS, matikan VPN atau Cloudflare WARP.
- Untuk demo, set `APP_DEBUG=false` di `.env`, lalu jalankan `php artisan config:clear`.
- Seeder demo hanya mengubah stok Susu UHT (BHN-003) menjadi 300 ml (0,3 liter). Jalankan ulang tepat sebelum demo.

## 4. Cara memasang APK

1. Salin `apk/kedai-kopi-senja.apk` ke HP (kabel, atau unggah ke Drive lalu unduh di HP).
2. Izinkan pemasangan dari sumber tidak dikenal, lalu pasang. Minimal Android 8.0.
3. Hubungkan HP dan laptop ke jaringan yang sama (Wi-Fi atau hotspot).
4. Di laptop, cek alamat IP dengan `ipconfig` (IPv4) dan jalankan server dengan `--host=0.0.0.0` (bagian 3).
   Bila HP tidak bisa menjangkau server, izinkan port 8000 di Windows Firewall:

   ```powershell
   New-NetFirewallRule -DisplayName "Kafe 8000" -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow
   ```

5. Di aplikasi, atur **Alamat server** menjadi `http://IP-LAPTOP:8000` (tab **Akun**, setelah masuk). Alamat bawaan `http://192.168.110.127:8000` hanya berlaku di jaringan tempat pengembangan.
   APK yang memanggil `localhost` tidak bisa menghubungi server dari HP.

Sumber APK ada di `apk-src` (proyek Capacitor; tampilan di `apk-src/www/index.html`). Membangun ulang (opsional,
perlu Android Studio; Capacitor memerlukan Java 21, yaitu JBR bawaan Android Studio):

```powershell
$env:JAVA_HOME = "C:\Program Files\Android\Android Studio\jbr"
```

```powershell
Set-Location apk-src; npx cap sync android; Set-Location android; .\gradlew.bat assembleDebug
```

Hasilnya ada di `apk-src/android/app/build/outputs/apk/debug/app-debug.apk`, lalu disalin ke `apk/kedai-kopi-senja.apk`.

## 5. Akun uji

| Peran | Login | Password |
|---|---|---|
| Kasir (web) | `kasir@kedaisenja.test` | `kasir12345` |
| Manajer (web) | `manajer@kedaisenja.test` | `manajer12345` |
| Pelanggan (APK) | `081200000001` | `passwordbaru1` |

Akun pelanggan dibuat lewat pendaftaran di APK (bukan seeder). Bila database dibuat ulang, daftarkan kembali nomor
di atas lewat layar Daftar. Lupa password pelanggan: Kasir membuka **Reset password pelanggan** di web, mencari nomor
HP, lalu menekan reset; pelanggan wajib menggantinya saat login berikutnya.

## 6. Letak berkas

| Isi | Letak |
|---|---|
| Migrasi | `database/migrations` |
| Impor data awal CSV | `app/Console/Commands/ImporDataAwal.php` (perintah `php artisan data:impor-awal`; juga lewat halaman Manajer **Impor CSV**) |
| Berkas CSV klien | `database/data/awal` (bahan, menu, resep) |
| Seeder akun staf | `database/seeders/StafSeeder.php` |
| Seeder data demo | `database/seeders/DemoSeeder.php` (perintah: `php artisan db:seed --class=DemoSeeder`; menyiapkan stok Susu UHT 300 ml, tanpa membuat pesanan) |
| Logika pesanan | `app/Services/PesananService.php` |
| Sisa porsi dan Habis | `app/Models/Menu.php` (`porsiTersedia`), `app/Services/KetersediaanMenu.php` (API) |
| Layanan Supplier | `app/Services/SupplierService.php` |
| Penyesuaian stok (O3) | `app/Http/Controllers/StokPenyesuaianController.php` |
| API | `routes/api.php`, `app/Http/Controllers/Api` |
| ERD | `docs/erd.md` (gambar: `docs/erd.png`) |
| UML class diagram | `docs/uml.md` (gambar: `docs/uml-class.png`) |
| Perancangan (use case, skenario, activity, urutan, hak akses, endpoint) | `docs/perancangan.md` (gambar: `docs/usecase.png`) |
| APK dan sumbernya | `apk/kedai-kopi-senja.apk`, `apk-src/` |

## 7. Keputusan Anda

**Akun dan data**
- Staf login dengan **email** dan password.
- **Nomor HP pelanggan:** hanya angka, diawali `08`, panjang 10 sampai 15 digit, unik. Nomor berisi huruf atau kurang
  dari 10 digit ditolak. Nama akun pelanggan sama dengan nomor HP.
- **Nama bahan unik:** disimpan juga sebagai `nama_kunci` (huruf kecil, spasi tepi dibuang) dengan constraint unik.
- **Kategori menu** dibatasi `Minuman` dan `Makanan` (sesuai data klien).
- Lupa password: Kasir mereset menjadi **password sementara 8 karakter acak**, tampil sekali di layar Kasir; akun
  ditandai `wajib_ganti_password` dan server menolak semua fitur lain sampai password diganti.

**Pesanan dan biaya**
- **Pembulatan:** service = round(subtotal × 5%); pajak = round((subtotal + service) × 10%). Uang bilangan bulat.
- **Perubahan harga:** harga item dan angka biaya disimpan di `pesanan_item` dan `pesanan` saat pesanan dibuat;
  struk dan laporan membaca angka tersimpan itu.
- **Pesanan dibatalkan tidak dihapus:** statusnya `Dibatalkan`, tidak dihitung di laporan penjualan, dan stok
  dikembalikan dari catatan `pesanan_bahan` (bukan dihitung ulang dari resep).
- **Status berjenjang:** server yang menentukan status berikutnya; browser tidak mengirim status tujuan.
- **Satu pesanan Baru per pelanggan** diperiksa di controller API (aturan khusus Pelanggan, bukan Kasir).
- **Menu Habis** tidak disimpan; dihitung dari stok setiap kali ditampilkan atau dipesan.
- **Stok kurang di APK:** ditampilkan kalimat umum sesuai desain **dan** rincian bahan yang kurang dari server,
  karena soal meminta bahan yang kurang ditampilkan dan aturan pesanan APK sama dengan Kasir.

**Data awal CSV**
- Satuan `kg` dan `liter` diubah ke `g` dan `ml`: stok dikali 1000, harga per satuan dibagi 1000.
- Nama bahan salah ketik di resep dicocokkan bila jaraknya **tepat 1 huruf** (levenshtein) dari nama bahan dan hanya
  ada **satu** kandidat. Ambang 2 sengaja tidak dipakai karena "Susu oat" (jarak 2) akan salah dicocokkan ke "Susu UHT".
- Bahan yang tidak terdaftar di `bahan.csv` ("Susu oat", "Sirup vanila") dilewati dan dilaporkan.
- Batas minimum awal setiap bahan 0. Impor ulang tidak menggandakan data dan tidak menimpa stok maupun harga bahan
  yang sudah ada.
- Hasil impor: 20 bahan, 14 menu, 63 resep (1 dicocokkan, 2 dilewati).

**Layanan Supplier**
- Dipanggil dari server saat halaman HPP dibuka. Harga dicocokkan lewat `kode` bahan (BHN-001, dst.), nama sebagai
  cadangan, dan harga hanya diperbarui bila satuan dasarnya sama. Bila layanan gagal atau key ditolak, harga terakhir
  yang tersimpan dipakai dan tanggal pembaruannya ditampilkan.
- **Asal harga bahan:** tidak diinput manual. Harga awal dari `bahan.csv` (dinormalkan ke g/ml/pcs), lalu diperbarui dari
  Supplier saat halaman HPP dibuka; bila layanan gagal, harga terakhir dipakai beserta tanggalnya. Karena itu form bahan
  hanya berisi nama, satuan, dan batas minimum.
- **Asal harga jual menu:** harga awal dari `menu.csv`; hanya Manajer yang mengubahnya lewat halaman Menu. Layanan
  Supplier tidak menyediakan harga menu. Perubahan hanya berlaku untuk pesanan berikutnya.
- Zona waktu aplikasi `Asia/Jakarta`.

**Fitur opsional yang dikerjakan**
- **O1 (Reguler dan Large):** hanya kategori Minuman. Large +Rp5.000; takaran bahan `g` dan `ml` dikali 1,5, bahan
  `pcs` tidak berubah. Ukuran disimpan di `pesanan_item.ukuran` (pesanan lama otomatis Reguler) dan tampil di struk.
- **O3 (penyesuaian stok):** selisih tidak boleh 0, alasan wajib, stok tidak boleh menjadi minus; setiap penyesuaian
  dicatat (bahan, selisih, alasan, siapa) di tabel `stok_penyesuaian`. Penyesuaian tidak mengubah `pesanan_bahan`.

## 8. Lembar self-check

Klaim "Ya" hanya untuk butir yang sudah saya uji (di web, lewat API, dan di HP).

| No | Butir penerimaan | Klaim | Langkah uji singkat |
|---|---|---|---|
| 1 | Kelola bahan, menu (bisa dinonaktifkan), resep | Ya | Manajer: `/bahan` (nama ganda ditolak), `/menu` (saklar aktif), `/resep` (tambah bahan dan takaran). |
| 2 | Stok masuk, batas minimum, daftar bahan menipis | Ya | Manajer: `/stok-masuk` catat pembelian, stok bertambah. Ubah batas minimum di `/bahan`, bahan muncul di Bahan menipis (`/laporan`). |
| 3 | Pesanan beberapa item, stok berkurang sesuai resep × porsi, menu tanpa resep atau nonaktif tidak bisa dipesan | Ya | Kasir: `/pesanan/create`, pesan beberapa item, cek stok di `/bahan`. Nonaktifkan satu menu: tidak bisa dipesan. |
| 4 | Pesanan ditolak bila bahan kurang, kebutuhan gabungan benar (inti) | Ya | Jalankan DemoSeeder. Latte × 3 ditolak ("Susu UHT, butuh 450 ml, sisa 300 ml"). 1 Latte + 1 Cokelat Panas (150 + 180 = 330 ml) ditolak. Stok tidak berubah. |
| 5 | Menu otomatis Habis bila bahan tidak cukup untuk 1 porsi | Ya | Habiskan satu bahan; menu itu berstatus Habis di halaman Kasir dan di APK (`/api/menu`). |
| 6 | Status berjenjang, loncat ditolak; batal hanya saat Baru, hanya Kasir, stok kembali | Ya | Kasir: `/pesanan`, Proses lalu Selesai. Batalkan pesanan Baru: stok kembali. Pesanan Diproses tidak bisa dibatalkan. |
| 7 | Struk: subtotal, service 5%, pajak 10%, total; struk lama tidak berubah (inti) | Ya | Pesan 1 Latte (28.000): service 1.400, pajak 2.940, total 32.340. Manajer ubah harga Latte; struk lama tetap. |
| 8 | HPP dan margin per menu dari resep | Ya | Manajer: `/hpp`. HPP = jumlah (takaran × harga per satuan dasar); margin = harga jual − HPP. |
| 9 | Laporan dan hak akses (termasuk alamat langsung) | Ya | Manajer: `/laporan` (penjualan per menu per periode, bahan menipis, margin). Kasir membuka `/laporan`, `/bahan`, `/menu`, `/resep`, `/stok-masuk`: 403. Manajer membuka `/pesanan`: 403. Tanpa login: diarahkan ke login. |
| 10 | Data awal CSV | Ya | `php artisan data:impor-awal` dari database kosong: 20 bahan, 14 menu, 63 resep (1 salah ketik dicocokkan, 2 dilewati); satuan dinormalkan; batas minimum 0; diulang tidak menggandakan. |
| 11 | Integrasi Supplier dengan API key, fallback harga terakhir dan tanggal | Ya | Buka `/hpp` (tanggal Supplier tampil). Ubah satu huruf `SUPPLIER_API_KEY`: halaman tidak error, harga lama dan tanggalnya tampil. |
| 12 | Akun pelanggan lengkap (APK) | Ya | APK: daftar, login, ganti password, logout. Kasir reset password (`/reset-password`); login dengan password sementara memaksa ganti password. |
| 13 | Pelanggan memesan dengan nomor meja, aturan sama dengan Kasir, satu pesanan Baru | Ya | APK: pilih meja 1 sampai 10 dan Tunai/QRIS, pesan. Meja di luar rentang ditolak. Pesanan kedua saat masih Baru ditolak. Stok kurang menampilkan bahan yang kurang. |
| 14 | Pelanggan memantau status dan struk sendiri | Ya | APK: tab Pesanan menampilkan status dan struk yang mengikuti perubahan dari Kasir. Pesanan akun lain tidak bisa dibuka (404). |

**Fitur opsional**

| Kode | Fitur | Dikerjakan | Langkah uji singkat |
|---|---|---|---|
| O1 | Ukuran Reguler dan Large | Ya | Kasir atau APK: pilih Large pada Minuman; harga +5.000; stok terpotong 1,5× untuk g dan ml; Large untuk Makanan ditolak; tampil "(Large)" di struk. |
| O2 | Promo happy hour | Tidak | |
| O3 | Penyesuaian stok | [ISI: Ya atau Tidak setelah diuji] | Manajer: `/stok-penyesuaian`, selisih negatif dan positif dengan alasan; selisih yang membuat stok minus ditolak; alasan kosong ditolak. |
| O4 | Laporan jam ramai | Tidak | |
| O5 | Reset password lewat email | Tidak | |

## 9. Belum selesai / keterbatasan

- Fitur opsional O2, O4, dan O5 tidak dikerjakan.
- Sisa porsi menu dihitung untuk ukuran Reguler; untuk Large angkanya bisa sedikit lebih banyak dari yang sebenarnya.
- Tampilan APK mengikuti desain Figma, tetapi belum dibandingkan piksel per piksel.
- APK memanggil server lewat HTTP biasa di jaringan lokal (Capacitor `cleartext` dan `allowMixedContent` aktif),
  bukan HTTPS. Token disimpan di penyimpanan lokal aplikasi. Alamat server harus disesuaikan dengan IP laptop.
- Bahan tidak punya fitur hapus (soal tidak memintanya; riwayat stok dan resep bergantung pada bahan).
- Harga Supplier diperbarui saat halaman HPP dibuka, bukan terjadwal otomatis.
- Impor ulang CSV mengembalikan takaran resep sesuai CSV.
- Tampilan web dirancang untuk layar desktop; tampilan layar HP untuk web tidak dibuat (tidak diwajibkan soal).
- Alamat server di APK hanya bisa diubah setelah masuk. Bila alamat bawaan tidak terjangkau di jaringan lain, ubah
  `SERVER_BAWAAN` di `apk-src/www/index.html` lalu bangun ulang APK (bagian 4).

## 10. Referensi dan Bantuan

**Sumber:** dokumentasi resmi Laravel (migrasi, Eloquent, validasi, Sanctum), Tailwind CSS, Capacitor, dan Android Studio.

**AI sebagai pendamping.** Saya memakai asisten AI (Claude dan satu asisten AI lain) sebagai pendamping belajar dan
pemberi rekomendasi kode: menjelaskan konsep (transaksi database, middleware, Sanctum), memberi contoh dan kerangka
kode, membantu membaca pesan error, dan membuat draf dokumentasi. Contoh kode tersebut saya salin ke editor,
jalankan, uji, dan perbaiki. Aturan bisnis di bagian 7 saya putuskan sendiri, dan seluruh pengujian di web, API,
dan HP saya jalankan sendiri. Beberapa berkas lebih banyak dibuat dengan bantuan AI (tampilan APK hibrid, sebagian
tampilan Blade, dan draf dokumentasi); untuk berkas-berkas itu saya memeriksa hasilnya, menjalankannya, dan
mempelajari cara kerjanya.

**Contoh perbaikan yang saya lakukan:** salah ketik pada nama method dan validasi (`findOrFall`, `withMassages`,
`exists:menu_id`, `Qris`), route yang tertinggal di luar grup hak akses, dan migrasi yang perlu dicek ulang saat
database dibuat dari nol.

**Bagian yang saya tulis atau ubah sendiri secara langsung:** [ISI: sebutkan hanya yang benar-benar kamu tulis atau
ubah sendiri, misalnya migrasi, model dan relasi, StafSeeder, kondisi di CekPeran, Menu::porsiTersedia, dan lainnya].

Saya mempelajari bagian-bagian kode inti (pembuatan pesanan, perhitungan kebutuhan bahan, struk, status dan
pembatalan, hak akses, Supplier, impor CSV) dan siap menjelaskannya saat wawancara.
