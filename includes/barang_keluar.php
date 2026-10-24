<?php

function input_awal_barang_keluar(): array
{
    return [
        'tanggal'      => date('Y-m-d'),
        'kode_barang'  => '',
        'jumlah'       => '',
        'nama_peminta' => '',
    ];
}

function ambil_input_barang_keluar(array $sumber): array
{
    return [
        'tanggal'      => ambil_teks($sumber, 'tanggal'),
        'kode_barang'  => ambil_teks($sumber, 'kode_barang'),
        'jumlah'       => ambil_teks($sumber, 'jumlah'),
        'nama_peminta' => ambil_teks($sumber, 'nama_peminta'),
    ];
}

function validasi_barang_keluar(mysqli $koneksi, array $input): array
{
    $error = [];

    $pesan = validasi_tanggal_transaksi($input['tanggal']);
    if ($pesan !== null) {
        $error['tanggal'] = $pesan;
    }

    if ($input['kode_barang'] === '') {
        $error['kode_barang'] = 'Pilih barang yang diminta.';
    } elseif (!barang_terdaftar($koneksi, $input['kode_barang'])) {
        $error['kode_barang'] = 'Barang tidak terdaftar di data barang.';
    }

    $pesan = validasi_jumlah_transaksi($input['jumlah']);
    if ($pesan !== null) {
        $error['jumlah'] = $pesan;
    }

    $pesan = validasi_teks_wajib($input['nama_peminta'], 'Nama peminta', 100);
    if ($pesan !== null) {
        $error['nama_peminta'] = $pesan;
    }

    return $error;
}

function proses_barang_keluar(mysqli $koneksi, array $input): array
{
    $jumlah = (int) $input['jumlah'];

    mysqli_begin_transaction($koneksi);

    try {
        $stok = ambil_stok_untuk_diperbarui($koneksi, $input['kode_barang']);

        if ($stok === null) {
            throw new RuntimeException('Barang ' . $input['kode_barang'] . ' tidak ditemukan saat diproses.');
        }

        if ($jumlah > $stok['jumlah_stok']) {
            mysqli_rollback($koneksi);

            return ['disetujui' => false, 'stok_tersedia' => $stok['jumlah_stok']];
        }

        $statement = mysqli_prepare(
            $koneksi,
            'INSERT INTO barang_keluar (tanggal, kode_barang, jumlah, nama_peminta) VALUES (?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param(
            $statement,
            'ssis',
            $input['tanggal'],
            $input['kode_barang'],
            $jumlah,
            $input['nama_peminta']
        );
        mysqli_stmt_execute($statement);
        $noTransaksi = (int) mysqli_insert_id($koneksi);
        mysqli_stmt_close($statement);

        kurangi_stok_barang($koneksi, $input['kode_barang'], $jumlah);

        mysqli_commit($koneksi);

        $sisaStok = $stok['jumlah_stok'] - $jumlah;

        return [
            'disetujui'    => true,
            'no_transaksi' => $noTransaksi,
            'sisa_stok'    => $sisaStok,
            'perlu_restok' => perlu_restok($sisaStok, $stok['stok_minimum']),
        ];
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        throw $e;
    }
}

function ambil_riwayat_barang_keluar(mysqli $koneksi): array
{
    $sql = 'SELECT bk.no_transaksi_keluar, bk.tanggal, bk.kode_barang, db.nama_barang, db.satuan,
                   bk.jumlah, bk.nama_peminta
            FROM barang_keluar AS bk
            JOIN data_barang AS db ON db.kode_barang = bk.kode_barang
            ORDER BY bk.tanggal DESC, bk.no_transaksi_keluar DESC';

    return mysqli_fetch_all(mysqli_query($koneksi, $sql), MYSQLI_ASSOC);
}