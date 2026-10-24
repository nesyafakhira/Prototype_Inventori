<?php

require_once __DIR__ . '/config/koneksi.php';

$koneksi = db_koneksi();

$hasil = mysqli_query($koneksi, 'SELECT DATABASE()');
$namaDatabase = mysqli_fetch_row($hasil)[0];
$versiServer = mysqli_get_server_info($koneksi);
$waktuSekarang = date('d-m-Y H:i:s');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Koneksi - Inventori Gudang</title>
</head>
<body>
    <h1>Sistem Informasi Inventori Gudang Suku Cadang Kendaraan</h1>
    <p>Koneksi database berhasil.</p>
    <ul>
        <li>Database: <?= htmlspecialchars($namaDatabase) ?></li>
        <li>Versi server: <?= htmlspecialchars($versiServer) ?></li>
        <li>Waktu server (WIB): <?= htmlspecialchars($waktuSekarang) ?></li>
    </ul>
</body>
</html>