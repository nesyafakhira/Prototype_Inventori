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