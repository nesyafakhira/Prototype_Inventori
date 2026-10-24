<?php

function input_awal_barang_masuk(): array
{
    return [
        'tanggal'           => date('Y-m-d'),
        'kode_barang'       => '',
        'jumlah'            => '',
        'nama_pemasok'      => '',
        'no_surat_jalan'    => '',
        'hasil_pemeriksaan' => '',
    ];
}

function ambil_input_barang_masuk(array $sumber): array
{
    return [
        'tanggal'           => ambil_teks($sumber, 'tanggal'),
        'kode_barang'       => ambil_teks($sumber, 'kode_barang'),
        'jumlah'            => ambil_teks($sumber, 'jumlah'),
        'nama_pemasok'      => ambil_teks($sumber, 'nama_pemasok'),
        'no_surat_jalan'    => ambil_teks($sumber, 'no_surat_jalan'),
        'hasil_pemeriksaan' => ambil_teks($sumber, 'hasil_pemeriksaan'),
    ];
}

function validasi_barang_masuk(mysqli $koneksi, array $input): array
{
    $error = [];

    $pesan = validasi_tanggal_transaksi($input['tanggal']);
    if ($pesan !== null) {
        $error['tanggal'] = $pesan;
    }

    if ($input['kode_barang'] === '') {
        $error['kode_barang'] = 'Pilih barang yang diterima.';
    } elseif (!barang_terdaftar($koneksi, $input['kode_barang'])) {
        $error['kode_barang'] = 'Barang tidak terdaftar di data barang.';
    }

    $pesan = validasi_jumlah_transaksi($input['jumlah']);
    if ($pesan !== null) {
        $error['jumlah'] = $pesan;
    }

    $pesan = validasi_teks_wajib($input['nama_pemasok'], 'Nama pemasok', 100);
    if ($pesan !== null) {
        $error['nama_pemasok'] = $pesan;
    }

    $pesan = validasi_teks_wajib($input['no_surat_jalan'], 'Nomor surat jalan', 50);
    if ($pesan !== null) {
        $error['no_surat_jalan'] = $pesan;
    }

    if (!in_array($input['hasil_pemeriksaan'], ['sesuai', 'tidak_sesuai'], true)) {
        $error['hasil_pemeriksaan'] = 'Pilih hasil pemeriksaan fisik barang.';
    }

    return $error;
}

function simpan_barang_masuk(mysqli $koneksi, array $input): int
{
    $jumlah = (int) $input['jumlah'];

    mysqli_begin_transaction($koneksi);

    try {
        $statement = mysqli_prepare(
            $koneksi,
            'INSERT INTO barang_masuk (tanggal, kode_barang, jumlah, nama_pemasok, no_surat_jalan)
             VALUES (?, ?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param(
            $statement,
            'ssiss',
            $input['tanggal'],
            $input['kode_barang'],
            $jumlah,
            $input['nama_pemasok'],
            $input['no_surat_jalan']
        );
        mysqli_stmt_execute($statement);
        $noTransaksi = (int) mysqli_insert_id($koneksi);
        mysqli_stmt_close($statement);

        tambah_stok_barang($koneksi, $input['kode_barang'], $jumlah);

        mysqli_commit($koneksi);

        return $noTransaksi;
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        throw $e;
    }
}

function ambil_riwayat_barang_masuk(mysqli $koneksi): array
{
    $sql = 'SELECT bm.no_transaksi_masuk, bm.tanggal, bm.kode_barang, db.nama_barang, db.satuan,
                   bm.jumlah, bm.nama_pemasok, bm.no_surat_jalan
            FROM barang_masuk AS bm
            JOIN data_barang AS db ON db.kode_barang = bm.kode_barang
            ORDER BY bm.tanggal DESC, bm.no_transaksi_masuk DESC';

    return mysqli_fetch_all(mysqli_query($koneksi, $sql), MYSQLI_ASSOC);
}