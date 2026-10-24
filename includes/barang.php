<?php

function ambil_daftar_barang(mysqli $koneksi, string $kataKunci = '', bool $hanyaPerluRestok = false): array
{
    $sql = 'SELECT kode_barang, nama_barang, kategori, satuan, jumlah_stok, stok_minimum
            FROM data_barang';
    $kondisi = [];
    $tipeParameter = '';
    $parameter = [];

    if ($kataKunci !== '') {
        $kondisi[] = '(kode_barang LIKE ? OR nama_barang LIKE ? OR kategori LIKE ?)';
        $pola = '%' . addcslashes($kataKunci, '%_\\') . '%';
        $tipeParameter .= 'sss';
        array_push($parameter, $pola, $pola, $pola);
    }

    if ($hanyaPerluRestok) {
        $kondisi[] = 'jumlah_stok <= stok_minimum';
    }

    if ($kondisi !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $kondisi);
    }

    $sql .= ' ORDER BY kode_barang';

    $statement = mysqli_prepare($koneksi, $sql);

    if ($parameter !== []) {
        mysqli_stmt_bind_param($statement, $tipeParameter, ...$parameter);
    }

    mysqli_stmt_execute($statement);
    $hasil = mysqli_stmt_get_result($statement);
    $daftarBarang = mysqli_fetch_all($hasil, MYSQLI_ASSOC);
    mysqli_stmt_close($statement);

    return $daftarBarang;
}

function hitung_ringkasan_stok(mysqli $koneksi): array
{
    $sql = 'SELECT COUNT(*) AS total_barang,
                   COALESCE(SUM(jumlah_stok <= stok_minimum), 0) AS perlu_restok,
                   COALESCE(SUM(jumlah_stok = 0), 0) AS stok_habis
            FROM data_barang';

    $baris = mysqli_fetch_assoc(mysqli_query($koneksi, $sql));

    return [
        'total_barang' => (int) $baris['total_barang'],
        'perlu_restok' => (int) $baris['perlu_restok'],
        'stok_habis'   => (int) $baris['stok_habis'],
    ];
}