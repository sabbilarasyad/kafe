# UML Class Diagram — Menu, Resep, Bahan, Pesanan

Diagram menggambarkan hubungan kelas model (`app/Models`) dan layanan pembuat pesanan
(`app/Services/PesananService.php`). Memakai Mermaid, tampil otomatis di GitHub.

```mermaid
classDiagram
    class Menu {
        +int id
        +string kode
        +string nama
        +string kategori
        +int harga
        +bool aktif
        +resepItems() HasMany
        +porsiTersedia() int
        +hpp() float
    }

    class ResepItem {
        +int id
        +int menu_id
        +int bahan_id
        +decimal takaran
        +menu() BelongsTo
        +bahan() BelongsTo
    }

    class Bahan {
        +int id
        +string kode
        +string nama
        +string nama_kunci
        +string satuan
        +decimal stok
        +decimal batas_minimum
        +decimal harga_per_satuan
        +date harga_diperbarui
        +resepItems() HasMany
    }

    class Pesanan {
        +int id
        +int user_id
        +int nomor_meja
        +string metode_bayar
        +string status
        +int subtotal
        +int service
        +int pajak
        +int total
        +items() HasMany
        +bahanTerpotong() HasMany
        +user() BelongsTo
    }

    class PesananItem {
        +int id
        +int pesanan_id
        +int menu_id
        +int qty
        +string ukuran
        +int harga_satuan
        +pesanan() BelongsTo
        +menu() BelongsTo
    }

    class PesananBahan {
        +int id
        +int pesanan_id
        +int bahan_id
        +decimal jumlah
        +pesanan() BelongsTo
        +bahan() BelongsTo
    }

    class StokPenyesuaian {
        +int id
        +int bahan_id
        +decimal selisih
        +string alasan
        +int user_id
        +bahan() BelongsTo
    }

    class PesananService {
        +int TAMBAHAN_LARGE
        +float PENGALI_LARGE
        +buat(pembuat, items, metodeBayar, nomorMeja) Pesanan
        -tolakBilaTidakBisaDipesan(items, menus) void
        -tolakUkuranTidakValid(items, menus) void
        -gabungKebutuhan(items, menus) array
        -cariKekurangan(kebutuhan) array
        -hargaSatuan(item, menus) int
        -hitungBiaya(subtotal) array
    }

    Menu "1" --> "1..*" ResepItem : memiliki resep
    Bahan "1" --> "*" ResepItem : dipakai di resep
    Pesanan "1" *-- "1..*" PesananItem : berisi
    Menu "1" --> "*" PesananItem : dipesan sebagai
    Pesanan "1" *-- "*" PesananBahan : mencatat potongan stok
    Bahan "1" --> "*" PesananBahan : terpotong dari
    Bahan "1" --> "*" StokPenyesuaian : disesuaikan
    PesananService ..> Pesanan : membuat
    PesananService ..> Menu : membaca resep
    PesananService ..> Bahan : memotong stok
```

## Penjelasan singkat

- **Menu** terdiri dari satu atau lebih **ResepItem**. Tiap baris resep menunjuk satu **Bahan** dan takarannya.
- **Pesanan** berisi satu atau lebih **PesananItem** (menu, jumlah, ukuran, dan harga saat itu).
- Saat pesanan dibuat, **PesananService** menghitung kebutuhan bahan dari seluruh item (ukuran Large: takaran g dan ml
  dikali 1,5, pcs tetap), memotong stok **Bahan**, dan mencatat potongannya di **PesananBahan**.
- `Menu::porsiTersedia()` menghitung sisa porsi dari stok bahan (bahan paling sedikit yang menentukan); menu otomatis
  Habis bila hasilnya 0.
- `Menu::hpp()` menjumlahkan takaran × harga bahan per satuan dasar; margin = harga jual − HPP.
- **StokPenyesuaian** mencatat penyesuaian stok (bahan rusak atau opname) dengan alasan wajib.
