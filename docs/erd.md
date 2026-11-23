# ERD — Kedai Kopi Senja

Database: SQLite (relasional). Sumber kebenarannya adalah folder `database/migrations`.

![ERD](erd.png)

Versi teks (Mermaid, tampil otomatis di GitHub):

```mermaid
erDiagram
    users ||--o{ pesanan : "membuat"
    users ||--o{ stok_masuk : "mencatat"
    users ||--o{ stok_penyesuaian : "mencatat"
    menu ||--o{ resep_item : "terdiri dari"
    bahan ||--o{ resep_item : "dipakai di"
    bahan ||--o{ stok_masuk : "dibeli"
    bahan ||--o{ stok_penyesuaian : "disesuaikan"
    pesanan ||--|{ pesanan_item : "berisi"
    menu ||--o{ pesanan_item : "dipesan sebagai"
    pesanan ||--o{ pesanan_bahan : "memotong stok"
    bahan ||--o{ pesanan_bahan : "terpotong dari"

    users {
        bigint id PK
        string name
        string email UK "boleh kosong"
        string password
        string role "kasir | manajer | pelanggan"
        string nomor_hp UK "boleh kosong, hanya angka"
        boolean wajib_ganti_password "default false"
    }

    bahan {
        bigint id PK
        string kode UK "boleh kosong, mis. BHN-001"
        string nama
        string nama_kunci UK "nama huruf kecil tanpa spasi tepi"
        string satuan "g | ml | pcs"
        decimal stok "default 0"
        decimal batas_minimum "default 0"
        decimal harga_per_satuan "boleh kosong"
        timestamp harga_diperbarui "boleh kosong"
    }

    menu {
        bigint id PK
        string kode UK "boleh kosong, mis. MNU-003"
        string nama
        string kategori "Minuman | Makanan"
        bigint harga "rupiah, bilangan bulat"
        boolean aktif "default true"
    }

    resep_item {
        bigint id PK
        bigint menu_id FK "cascade"
        bigint bahan_id FK "restrict"
        decimal takaran "satuan dasar bahan"
    }

    stok_masuk {
        bigint id PK
        date tanggal
        bigint bahan_id FK "restrict"
        decimal jumlah
        bigint user_id FK "restrict"
    }

    stok_penyesuaian {
        bigint id PK
        bigint bahan_id FK "restrict"
        decimal selisih "negatif atau positif"
        string alasan "wajib"
        bigint user_id FK "restrict"
    }

    pesanan {
        bigint id PK
        bigint user_id FK "pembuat: kasir atau pelanggan"
        tinyint nomor_meja "boleh kosong, 1 sampai 10"
        string metode_bayar "Tunai | QRIS"
        string status "Baru | Diproses | Selesai | Dibatalkan"
        bigint subtotal
        bigint service
        bigint pajak
        bigint total
    }

    pesanan_item {
        bigint id PK
        bigint pesanan_id FK
        bigint menu_id FK "restrict"
        int qty
        string ukuran "Reguler | Large"
        bigint harga_satuan "harga saat pesanan dibuat"
    }

    pesanan_bahan {
        bigint id PK
        bigint pesanan_id FK
        bigint bahan_id FK "restrict"
        decimal jumlah "stok yang terpotong"
    }
```

Semua tabel juga memiliki `created_at` dan `updated_at`. Tabel Sanctum `personal_access_tokens` (token API) dan tabel
bawaan Laravel (`cache`, `jobs`, `sessions`, dan lain-lain) tidak digambar.

## Constraint penting

| Tabel | Constraint | Alasan |
|---|---|---|
| `bahan` | `nama_kunci` unik | Nama bahan tidak boleh ganda; beda huruf besar/kecil atau spasi tepi dianggap sama. |
| `users` | `nomor_hp` unik | Satu nomor HP hanya untuk satu akun pelanggan. |
| `resep_item` | unik (`menu_id`, `bahan_id`) | Satu bahan hanya satu baris dalam resep satu menu. |
| `pesanan_bahan` | unik (`pesanan_id`, `bahan_id`) | Kebutuhan bahan digabung per pesanan, satu baris per bahan. |
| `resep_item.menu_id` | `cascade` | Resep tidak berarti tanpa menunya. |
| Foreign key lain | `restrict` | Riwayat pembelian, struk, laporan, dan penyesuaian tidak hilang karena penghapusan data induk. |

## Catatan desain

- Uang disimpan sebagai bilangan bulat (rupiah), bukan desimal, supaya tidak ada selisih pembulatan.
- `pesanan_item.harga_satuan` dan angka di `pesanan` (subtotal, service, pajak, total) dibekukan saat pesanan dibuat,
  sehingga struk dan laporan lama tidak berubah ketika harga menu diubah.
- Status "Habis" pada menu tidak disimpan. Nilainya dihitung dari stok bahan setiap kali dibutuhkan.
- `pesanan_bahan` mencatat stok yang benar-benar terpotong, dipakai untuk mengembalikan persis jumlah itu saat
  pesanan dibatalkan.
- Harga bahan tidak diinput manual: berasal dari CSV (awal) lalu diperbarui dari layanan Supplier.

## Hubungan dengan rancangan awal (class diagram di PDF)

| Rancangan awal | Tabel di sistem |
|---|---|
| `User` | `users` |
| `Order` | `pesanan` |
| `OrderDetail` | `pesanan_item` |
| `Menu` | `menu` |
| `Recipe` + `RecipeDetail` | `resep_item` |
| `Ingredient` | `bahan` |
| `StockPurchase` + `StockPurchaseDetail` | `stok_masuk` |
| (tidak ada) | `pesanan_bahan`, `stok_penyesuaian` |

Alasan perbedaan dicatat di `docs/perancangan.md` bagian 2.
