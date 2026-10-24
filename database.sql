SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS db_inventori_gudang
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_inventori_gudang;

DROP TABLE IF EXISTS barang_keluar;
DROP TABLE IF EXISTS barang_masuk;
DROP TABLE IF EXISTS data_barang;

-- Bagian D1 Data Barang
CREATE TABLE data_barang (
    kode_barang  VARCHAR(20)  NOT NULL,
    nama_barang  VARCHAR(100) NOT NULL,
    kategori     VARCHAR(50)  NOT NULL,
    satuan       VARCHAR(20)  NOT NULL,
    jumlah_stok  INT          NOT NULL DEFAULT 0,
    stok_minimum INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (kode_barang),
    CONSTRAINT chk_data_barang_jumlah_stok  CHECK (jumlah_stok >= 0),
    CONSTRAINT chk_data_barang_stok_minimum CHECK (stok_minimum >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bagian D2: Barang Masuk
CREATE TABLE barang_masuk (
    no_transaksi_masuk INT          NOT NULL AUTO_INCREMENT,
    tanggal            DATE         NOT NULL,
    kode_barang        VARCHAR(20)  NOT NULL,
    jumlah             INT          NOT NULL,
    nama_pemasok       VARCHAR(100) NOT NULL,
    no_surat_jalan     VARCHAR(50)  NOT NULL,
    PRIMARY KEY (no_transaksi_masuk),
    CONSTRAINT fk_barang_masuk_data_barang
        FOREIGN KEY (kode_barang) REFERENCES data_barang (kode_barang)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT chk_barang_masuk_jumlah CHECK (jumlah > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bagian D3: Barang Keluar
CREATE TABLE barang_keluar (
    no_transaksi_keluar INT          NOT NULL AUTO_INCREMENT,
    tanggal             DATE         NOT NULL,
    kode_barang         VARCHAR(20)  NOT NULL,
    jumlah              INT          NOT NULL,
    nama_peminta        VARCHAR(100) NOT NULL,
    PRIMARY KEY (no_transaksi_keluar),
    CONSTRAINT fk_barang_keluar_data_barang
        FOREIGN KEY (kode_barang) REFERENCES data_barang (kode_barang)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT chk_barang_keluar_jumlah CHECK (jumlah > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data contoh tabel data barang
INSERT INTO data_barang (kode_barang, nama_barang, kategori, satuan, jumlah_stok, stok_minimum) VALUES
('PLM-001', 'Oli Mesin 10W-40 1 Liter',     'Pelumas',     'botol', 48, 20),
('PLM-002', 'Oli Gardan 120 ml',            'Pelumas',     'botol', 15, 15),
('FLT-001', 'Filter Udara',                 'Filter',      'pcs',   25, 10),
('FLT-002', 'Filter Oli',                   'Filter',      'pcs',    6, 10),
('REM-001', 'Kampas Rem Depan',             'Rem',         'set',   30, 12),
('REM-002', 'Minyak Rem DOT 4',             'Rem',         'botol',  0,  8),
('KLS-001', 'Busi Iridium',                 'Kelistrikan', 'pcs',   60, 20),
('KLS-002', 'Aki Kering 12V 5Ah',           'Kelistrikan', 'unit',   9,  5),
('TRM-001', 'Rantai Roda',                  'Transmisi',   'set',   12,  5),
('TRM-002', 'Gear Set Depan-Belakang',      'Transmisi',   'set',    4,  5),
('SPN-001', 'Shockbreaker Belakang',        'Suspensi',    'pcs',    7,  4);