<?php

date_default_timezone_set('Asia/Jakarta');

function db_koneksi(): mysqli
{
    static $koneksi = null;

    if ($koneksi !== null) {
        return $koneksi;
    }

    $fileConfig = __DIR__ . '/config.php';
    if (!file_exists($fileConfig)) {
        http_response_code(500);
        exit('File config/config.php belum ada. Salin dari config/config.example.php lalu sesuaikan isinya.');
    }

    $config = require $fileConfig;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $koneksi = mysqli_connect(
            $config['db_host'],
            $config['db_user'],
            $config['db_pass'],
            $config['db_name'],
            (int) $config['db_port']
        );
        mysqli_set_charset($koneksi, 'utf8mb4');
    } catch (mysqli_sql_exception $e) {
        error_log('Koneksi database gagal: ' . $e->getMessage());
        http_response_code(500);
        exit('Koneksi ke database gagal. Pastikan MySQL di XAMPP sudah berjalan dan isi config/config.php sudah benar.');
    }

    return $koneksi;
}