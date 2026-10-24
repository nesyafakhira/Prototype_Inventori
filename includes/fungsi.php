<?php

function e(mixed $nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

function perlu_restok(int $jumlahStok, int $stokMinimum): bool
{
    return $jumlahStok <= $stokMinimum;
}

function info_status_stok(int $jumlahStok, int $stokMinimum): array
{
    if ($jumlahStok === 0) {
        return ['label' => 'Habis', 'kelas' => 'text-bg-danger'];
    }

    if (perlu_restok($jumlahStok, $stokMinimum)) {
        return ['label' => 'Perlu Restok', 'kelas' => 'text-bg-warning'];
    }

    return ['label' => 'Aman', 'kelas' => 'text-bg-success'];
}

function format_tanggal(string $tanggal): string
{
    return date('d-m-Y', strtotime($tanggal));
}

function ambil_teks(array $sumber, string $kunci): string
{
    $nilai = $sumber[$kunci] ?? '';

    return is_string($nilai) ? trim($nilai) : '';
}

function validasi_tanggal_transaksi(string $tanggal): ?string
{
    if ($tanggal === '') {
        return 'Tanggal wajib diisi.';
    }

    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tanggal, $bagian)
        || !checkdate((int) $bagian[2], (int) $bagian[3], (int) $bagian[1])) {
        return 'Format tanggal tidak valid.';
    }

    if ($tanggal > date('Y-m-d')) {
        return 'Tanggal tidak boleh melebihi hari ini.';
    }

    return null;
}

function validasi_jumlah_transaksi(string $jumlah): ?string
{
    if ($jumlah === '') {
        return 'Jumlah wajib diisi.';
    }

    $angka = filter_var($jumlah, FILTER_VALIDATE_INT);

    if ($angka === false) {
        return 'Jumlah harus berupa bilangan bulat.';
    }

    if ($angka <= 0) {
        return 'Jumlah harus lebih dari nol.';
    }

    if ($angka > 2147483647) {
        return 'Jumlah terlalu besar.';
    }

    return null;
}

function validasi_teks_wajib(string $nilai, string $label, int $panjangMaksimum): ?string
{
    if ($nilai === '') {
        return $label . ' wajib diisi.';
    }

    if (mb_strlen($nilai) > $panjangMaksimum) {
        return $label . ' maksimal ' . $panjangMaksimum . ' karakter.';
    }

    return null;
}

function kelas_error(array $error, string $kolom): string
{
    return isset($error[$kolom]) ? ' is-invalid' : '';
}

function pesan_error(array $error, string $kolom): string
{
    if (!isset($error[$kolom])) {
        return '';
    }

    return '<div class="invalid-feedback d-block">' . e($error[$kolom]) . '</div>';
}

function mulai_sesi(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function set_flash(string $tipe, string $pesan): void
{
    mulai_sesi();

    $tipeValid = ['success', 'danger', 'warning', 'info'];
    $_SESSION['flash'] = [
        'tipe'  => in_array($tipe, $tipeValid, true) ? $tipe : 'info',
        'pesan' => $pesan,
    ];
}

function ambil_flash(): ?array
{
    mulai_sesi();

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function csrf_token(): string
{
    mulai_sesi();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_valid(mixed $token): bool
{
    mulai_sesi();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}