# Sistem Informasi Inventori Gudang Suku Cadang Kendaraan

Sistem mencatat pergerakan suku cadang di satu gudang: penerimaan barang dari pemasok,
pengeluaran barang berdasarkan permintaan Bagian Penjualan, serta pemantauan stok
dengan peringatan ketika stok mencapai atau berada di bawah batas minimum.

## Tech Stack

- PHP 8.x (prosedural, ekstensi mysqli)
- MySQL / MariaDB
- HTML, CSS, JavaScript, Bootstrap 5.3 
- XAMPP untuk lingkungan lokal
- Git dan GitHub

## Instalasi

1. Install XAMPP, lalu jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
2. Add folder `Prototype_Inventori` di `C:\xampp\htdocs\`,
   atau clone repo ini ke folder tersebut.
3. Salin `config/config.example.php` menjadi `config/config.php`,
   lalu sesuaikan username dan password MySQL bila berbeda.
4. Buka `http://localhost/phpmyadmin`, pilih tab **Import**,
   lalu import file `database.sql`. Database `db_inventori_gudang`
   beserta tabel dan data contohnya akan dibuat otomatis.
5. Buka `http://localhost/Prototype_Inventori/`.

> Import ulang `database.sql` akan menghapus seluruh data dan mengembalikan
> database ke kondisi awal.

## Aturan Bisnis yang Diterapkan

1. Kode barang unik dan wajib diisi.
2. Transaksi hanya untuk barang yang terdaftar di Data Barang.
3. Jumlah barang masuk dan keluar harus bilangan bulat lebih dari nol.
4. Tanggal transaksi tidak boleh melebihi hari ini.
5. Barang masuk hanya dicatat jika hasil pemeriksaan fisik **Sesuai**.
6. Barang keluar ditolak jika jumlah melebihi stok tersedia; stok tidak pernah negatif.
7. Penyimpanan transaksi dan perubahan stok dilakukan dalam satu DB transaction.
8. Barang ditandai **Perlu Restok** jika `jumlah_stok <= stok_minimum`.

## Keamanan Dasar

- Prepared statement untuk semua query yang memakai input pengguna.
- `htmlspecialchars` untuk semua data yang ditampilkan.
- CSRF pada setiap form.
- credential database disimpan di `config/config.php` yang tidak di-commit.

Fitur autentikasi dan otorisasi peran belum diterapkan, sehingga prototype ini
hanya untuk demo dan belum layak digunakan IRL.
