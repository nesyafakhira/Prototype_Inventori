<?php

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';
require_once __DIR__ . '/includes/barang.php';
require_once __DIR__ . '/includes/barang_keluar.php';

mulai_sesi();
$koneksi = db_koneksi();

$input = input_awal_barang_keluar();
$error = [];
$statusPermintaan = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Sesi formulir sudah tidak berlaku. Silakan isi ulang formulir.');
        redirect('barang_keluar.php');
    }

    $input = ambil_input_barang_keluar($_POST);
    $error = validasi_barang_keluar($koneksi, $input);

    if ($error !== []) {
        $statusPermintaan = 'Ditolak: data tidak valid. Periksa kembali isian yang ditandai merah.';
    } else {
        try {
            $hasil = proses_barang_keluar($koneksi, $input);

            if ($hasil['disetujui']) {
                $pesan = sprintf(
                    'Disetujui: barang keluar No. %d tercatat. Stok %s berkurang %d, sisa %d.',
                    $hasil['no_transaksi'],
                    $input['kode_barang'],
                    (int) $input['jumlah'],
                    $hasil['sisa_stok']
                );

                if ($hasil['perlu_restok']) {
                    $pesan .= ' Stok barang ini sekarang berada pada atau di bawah batas minimum dan perlu restok.';
                }

                set_flash($hasil['perlu_restok'] ? 'warning' : 'success', $pesan);
                redirect('barang_keluar.php');
            }

            $statusPermintaan = sprintf(
                'Ditolak: stok tidak cukup. Diminta %d, stok tersedia %d. Stok tidak berubah.',
                (int) $input['jumlah'],
                $hasil['stok_tersedia']
            );
        } catch (Throwable $e) {
            error_log('Gagal memproses barang keluar: ' . $e->getMessage());
            $statusPermintaan = 'Transaksi gagal disimpan karena kesalahan sistem. Tidak ada data maupun stok yang berubah.';
        }
    }
}

$daftarBarang = ambil_daftar_barang($koneksi);
$riwayatKeluar = ambil_riwayat_barang_keluar($koneksi);

$judulHalaman = 'Barang Keluar';
$halamanAktif = 'barang_keluar';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Barang Keluar</h1>
    <p class="text-muted mb-0">Proses permintaan suku cadang dari Bagian Penjualan berdasarkan stok yang tersedia.</p>
</div>

<?php if ($statusPermintaan !== null): ?>
    <div class="alert alert-danger" role="alert"><?= e($statusPermintaan) ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header fw-semibold">Formulir Permintaan Barang</div>
    <div class="card-body">
        <form method="post" action="barang_keluar.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="tanggal" class="form-label">Tanggal Pengeluaran</label>
                    <input type="date" id="tanggal" name="tanggal" required
                           class="form-control<?= kelas_error($error, 'tanggal') ?>"
                           value="<?= e($input['tanggal']) ?>" max="<?= e(date('Y-m-d')) ?>">
                    <?= pesan_error($error, 'tanggal') ?>
                </div>

                <div class="col-md-8">
                    <label for="kode_barang" class="form-label">Barang</label>
                    <select id="kode_barang" name="kode_barang" required
                            class="form-select<?= kelas_error($error, 'kode_barang') ?>">
                        <option value="">-- Pilih barang --</option>
                        <?php foreach ($daftarBarang as $barang): ?>
                            <option value="<?= e($barang['kode_barang']) ?>"
                                <?= $barang['kode_barang'] === $input['kode_barang'] ? 'selected' : '' ?>>
                                <?= e($barang['kode_barang'] . ' - ' . $barang['nama_barang']
                                    . ' (stok: ' . $barang['jumlah_stok'] . ' ' . $barang['satuan'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= pesan_error($error, 'kode_barang') ?>
                </div>

                <div class="col-md-4">
                    <label for="jumlah" class="form-label">Jumlah Diminta</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" step="1" required
                           class="form-control<?= kelas_error($error, 'jumlah') ?>"
                           value="<?= e($input['jumlah']) ?>">
                    <?= pesan_error($error, 'jumlah') ?>
                </div>

                <div class="col-md-8">
                    <label for="nama_peminta" class="form-label">Nama Peminta (Bagian Penjualan)</label>
                    <input type="text" id="nama_peminta" name="nama_peminta" maxlength="100" required
                           class="form-control<?= kelas_error($error, 'nama_peminta') ?>"
                           value="<?= e($input['nama_peminta']) ?>">
                    <?= pesan_error($error, 'nama_peminta') ?>
                </div>
            </div>

            <div class="form-text mt-3">
                Permintaan hanya disetujui jika jumlah tidak melebihi stok terkini di gudang.
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Proses Permintaan</button>
                <a href="barang_keluar.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Riwayat Barang Keluar</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th class="kolom-angka">Jumlah</th>
                    <th>Nama Peminta</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($riwayatKeluar === []): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada transaksi barang keluar.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($riwayatKeluar as $transaksi): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($transaksi['no_transaksi_keluar']) ?></td>
                        <td><?= e(format_tanggal($transaksi['tanggal'])) ?></td>
                        <td><?= e($transaksi['kode_barang']) ?></td>
                        <td><?= e($transaksi['nama_barang']) ?></td>
                        <td class="kolom-angka"><?= e($transaksi['jumlah'] . ' ' . $transaksi['satuan']) ?></td>
                        <td><?= e($transaksi['nama_peminta']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>