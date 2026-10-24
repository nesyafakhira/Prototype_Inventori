<?php

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';
require_once __DIR__ . '/includes/barang.php';
require_once __DIR__ . '/includes/barang_masuk.php';

mulai_sesi();
$koneksi = db_koneksi();

$input = input_awal_barang_masuk();
$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Sesi formulir sudah tidak berlaku. Silakan isi ulang formulir.');
        redirect('barang_masuk.php');
    }

    $input = ambil_input_barang_masuk($_POST);
    $error = validasi_barang_masuk($koneksi, $input);

    if ($error === [] && $input['hasil_pemeriksaan'] === 'tidak_sesuai') {
        set_flash('danger', 'Penerimaan ditolak karena hasil pemeriksaan tidak sesuai. Transaksi tidak dicatat dan stok tidak berubah.');
        redirect('barang_masuk.php');
    }

    if ($error === []) {
        try {
            $noTransaksi = simpan_barang_masuk($koneksi, $input);
            set_flash('success', sprintf(
                'Barang masuk No. %d berhasil dicatat. Stok %s bertambah %d.',
                $noTransaksi,
                $input['kode_barang'],
                (int) $input['jumlah']
            ));
            redirect('barang_masuk.php');
        } catch (Throwable $e) {
            error_log('Gagal menyimpan barang masuk: ' . $e->getMessage());
            $error['umum'] = 'Transaksi gagal disimpan karena kesalahan sistem. Tidak ada data maupun stok yang berubah.';
        }
    }
}

$daftarBarang = ambil_daftar_barang($koneksi);
$riwayatMasuk = ambil_riwayat_barang_masuk($koneksi);

$judulHalaman = 'Barang Masuk';
$halamanAktif = 'barang_masuk';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Barang Masuk</h1>
    <p class="text-muted mb-0">Catat suku cadang dari pemasok setelah diperiksa Admin Gudang.</p>
</div>

<?php if (isset($error['umum'])): ?>
    <div class="alert alert-danger" role="alert"><?= e($error['umum']) ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header fw-semibold">Formulir Penerimaan Barang</div>
    <div class="card-body">
        <form method="post" action="barang_masuk.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="tanggal" class="form-label">Tanggal Penerimaan</label>
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
                    <label for="jumlah" class="form-label">Jumlah Diterima</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" step="1" required
                           class="form-control<?= kelas_error($error, 'jumlah') ?>"
                           value="<?= e($input['jumlah']) ?>">
                    <?= pesan_error($error, 'jumlah') ?>
                </div>

                <div class="col-md-4">
                    <label for="nama_pemasok" class="form-label">Nama Pemasok</label>
                    <input type="text" id="nama_pemasok" name="nama_pemasok" maxlength="100" required
                           class="form-control<?= kelas_error($error, 'nama_pemasok') ?>"
                           value="<?= e($input['nama_pemasok']) ?>">
                    <?= pesan_error($error, 'nama_pemasok') ?>
                </div>

                <div class="col-md-4">
                    <label for="no_surat_jalan" class="form-label">No. Surat Jalan</label>
                    <input type="text" id="no_surat_jalan" name="no_surat_jalan" maxlength="50" required
                           class="form-control<?= kelas_error($error, 'no_surat_jalan') ?>"
                           value="<?= e($input['no_surat_jalan']) ?>">
                    <?= pesan_error($error, 'no_surat_jalan') ?>
                </div>

                <div class="col-12">
                    <span class="form-label d-block">Hasil Pemeriksaan Fisik</span>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input<?= kelas_error($error, 'hasil_pemeriksaan') ?>" type="radio"
                               name="hasil_pemeriksaan" id="hasil_sesuai" value="sesuai" required
                            <?= $input['hasil_pemeriksaan'] === 'sesuai' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="hasil_sesuai">Sesuai</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input<?= kelas_error($error, 'hasil_pemeriksaan') ?>" type="radio"
                               name="hasil_pemeriksaan" id="hasil_tidak_sesuai" value="tidak_sesuai"
                            <?= $input['hasil_pemeriksaan'] === 'tidak_sesuai' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="hasil_tidak_sesuai">Tidak Sesuai</label>
                    </div>
                    <?= pesan_error($error, 'hasil_pemeriksaan') ?>
                    <div class="form-text">
                        Barang hanya dicatat dan stok hanya bertambah jika fisik barang sesuai dengan surat jalan.
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan Barang Masuk</button>
                <a href="barang_masuk.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Riwayat Barang Masuk</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th class="kolom-angka">Jumlah</th>
                    <th>Pemasok</th>
                    <th>No. Surat Jalan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($riwayatMasuk === []): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi barang masuk.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($riwayatMasuk as $transaksi): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($transaksi['no_transaksi_masuk']) ?></td>
                        <td><?= e(format_tanggal($transaksi['tanggal'])) ?></td>
                        <td><?= e($transaksi['kode_barang']) ?></td>
                        <td><?= e($transaksi['nama_barang']) ?></td>
                        <td class="kolom-angka"><?= e($transaksi['jumlah'] . ' ' . $transaksi['satuan']) ?></td>
                        <td><?= e($transaksi['nama_pemasok']) ?></td>
                        <td><?= e($transaksi['no_surat_jalan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>