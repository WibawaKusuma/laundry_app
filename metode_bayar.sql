-- ============================================
-- FITUR METODE PEMBAYARAN - LAUNDRY APP
-- Jalankan query ini di phpMyAdmin
-- ============================================

-- 1. Buat Tabel Master Metode Bayar
CREATE TABLE metode_bayar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- 2. Insert Data Default
INSERT INTO metode_bayar (nama) VALUES ('Tunai'), ('QRIS'), ('Transfer');

-- 3. Tambah Field id_metode_bayar di Tabel Transaksi
ALTER TABLE transaksi ADD COLUMN id_metode_bayar INT NULL AFTER dibayar;
