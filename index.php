<?php

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';
require_once __DIR__ . '/includes/barang.php';

$koneksi = db_koneksi();

$kataKunci = is_string($_GET['cari'] ?? null) ? mb_substr(trim($_GET['cari']), 0, 100) : '';
$filterStatus = ($_GET['status'] ?? '') === 'restok' ? 'restok' : '';

$daftarBarang = ambil_daftar_barang($koneksi, $kataKunci, $filterStatus === 'restok');
$ringkasan = hitung_ringkasan_stok($koneksi);

$judulHalaman = 'Daftar Barang';
$halamanAktif = 'barang';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Daftar Barang</h1>
    <p class="text-muted mb-0">Informasi stok suku cadang terkini di gudang.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card kartu-ringkasan h-100">
            <div class="card-body">
                <div class="text-muted small">Jenis barang</div>
                <div class="angka"><?= e($ringkasan['total_barang']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card kartu-ringkasan h-100">
            <div class="card-body">
                <div class="text-muted small">Perlu restok</div>
                <div class="angka text-warning-emphasis"><?= e($ringkasan['perlu_restok']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card kartu-ringkasan h-100">
            <div class="card-body">
                <div class="text-muted small">Stok habis</div>
                <div class="angka text-danger"><?= e($ringkasan['stok_habis']) ?></div>
            </div>
        </div>
    </div>
</div>

<?php if ($ringkasan['perlu_restok'] > 0): ?>
    <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
        <span>
            <strong><?= e($ringkasan['perlu_restok']) ?> barang</strong>
            berada pada atau di bawah stok minimum dan perlu restok.
        </span>
        <a href="index.php?status=restok" class="btn btn-sm btn-outline-dark">Lihat barang</a>
    </div>
<?php endif; ?>

<form method="get" action="index.php" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="search" name="cari" class="form-control" maxlength="100"
               placeholder="Cari kode, nama, atau kategori" value="<?= e($kataKunci) ?>">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua status</option>
            <option value="restok" <?= $filterStatus === 'restok' ? 'selected' : '' ?>>Perlu restok</option>
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">Cari</button>
        <a href="index.php" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Suku Cadang</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th class="kolom-angka">Stok</th>
                    <th class="kolom-angka">Stok Min.</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarBarang === []): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Tidak ada barang yang sesuai pencarian.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($daftarBarang as $nomor => $barang): ?>
                    <?php $status = info_status_stok((int) $barang['jumlah_stok'], (int) $barang['stok_minimum']); ?>
                    <tr>
                        <td><?= $nomor + 1 ?></td>
                        <td class="fw-semibold"><?= e($barang['kode_barang']) ?></td>
                        <td><?= e($barang['nama_barang']) ?></td>
                        <td><?= e($barang['kategori']) ?></td>
                        <td><?= e($barang['satuan']) ?></td>
                        <td class="kolom-angka"><?= e($barang['jumlah_stok']) ?></td>
                        <td class="kolom-angka"><?= e($barang['stok_minimum']) ?></td>
                        <td><span class="badge <?= e($status['kelas']) ?>"><?= e($status['label']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer small text-muted">
        Menampilkan <?= count($daftarBarang) ?> dari <?= e($ringkasan['total_barang']) ?> barang.
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>