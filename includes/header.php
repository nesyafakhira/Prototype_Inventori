<?php

require_once __DIR__ . '/fungsi.php';

$judulHalaman = $judulHalaman ?? 'Inventori Gudang';
$halamanAktif = $halamanAktif ?? '';

$menuNavigasi = [
    'barang' => ['label' => 'Daftar Barang', 'url' => 'index.php'],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judulHalaman) ?> - Inventori Gudang Suku Cadang</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="index.php">Inventori Gudang</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navUtama"
                aria-controls="navUtama" aria-expanded="false" aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navUtama">
            <ul class="navbar-nav ms-auto">
                <?php foreach ($menuNavigasi as $kunci => $menu): ?>
                    <?php $aktif = $kunci === $halamanAktif; ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $aktif ? ' active' : '' ?>" href="<?= e($menu['url']) ?>"
                           <?= $aktif ? 'aria-current="page"' : '' ?>><?= e($menu['label']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4 flex-grow-1">