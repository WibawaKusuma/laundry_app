-- =========================================================
-- ISLaundry Multi-Branch Seed Master
-- =========================================================
-- Jalankan file ini setelah schema.sql pada database cabang baru.
-- File ini HANYA mengisi data master awal.
-- File ini TIDAK mengisi data operasional:
--   - m_pelanggan
--   - transaksi
--   - transaksi_detail
--   - pengeluaran

SET NAMES utf8mb4;

INSERT INTO `config` (`config_key`, `config_value`) VALUES
('company_name', 'LAUNDRY APP'),
('company_address', 'Jl. Mawar Melati No. 123, Tabanan, Bali'),
('company_phone', '0812-3456-7890'),
('company_tagline', 'Layanan Laundry Kilat'),
('company_logo', 'assets/image/logo.png')
ON DUPLICATE KEY UPDATE `config_value` = VALUES(`config_value`);

INSERT INTO `m_metode_bayar` (`id`, `nama`, `is_active`) VALUES
(1, 'Tunai', 1),
(2, 'QRIS', 1),
(3, 'Transfer', 1)
ON DUPLICATE KEY UPDATE
`nama` = VALUES(`nama`),
`is_active` = VALUES(`is_active`);

INSERT INTO `m_kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Antar Jemput'),
(2, 'Outlet'),
(3, 'Satuan Khusus'),
(4, 'Pakaian Bayi')
ON DUPLICATE KEY UPDATE `nama_kategori` = VALUES(`nama_kategori`);

INSERT INTO `m_satuan` (`id_satuan`, `nama_satuan`) VALUES
(1, 'KG'),
(2, 'PCS'),
(3, 'SET'),
(4, 'PASANG'),
(5, 'METER'),
(6, 'UNIT')
ON DUPLICATE KEY UPDATE `nama_satuan` = VALUES(`nama_satuan`);

INSERT INTO `m_users` (`id`, `username`, `password`, `role`, `name`) VALUES
(1, 'admin', '$2y$10$3LCgYWwVQHLo2KZ.hefhn.fEaxTuRfX2odHkXTEjwoK.ZeiG7OxwW', 'admin', 'Administrator'),
(2, 'kasir', '$2y$10$3RaDDTGuGLklHYo0WCXhZ.B43soSCKQTbg.HrsS1q478sYJNY88r.', 'kasir', 'Siti Kasir'),
ON DUPLICATE KEY UPDATE
`username` = VALUES(`username`),
`password` = VALUES(`password`),
`role` = VALUES(`role`),
`name` = VALUES(`name`);

INSERT INTO `m_paket_laundry` (`id_paket_laundry`, `id_kategori`, `nama_paket`, `id_satuan`, `harga`, `durasi_jam`) VALUES
(3, 2, 'cuci setrika', 1, 10000, 72),
(4, 1, 'setrika', 2, 5000, 24),
(5, 4, 'Cuci Komplit', 1, 18000, 4)
ON DUPLICATE KEY UPDATE
`id_kategori` = VALUES(`id_kategori`),
`nama_paket` = VALUES(`nama_paket`),
`id_satuan` = VALUES(`id_satuan`),
`harga` = VALUES(`harga`),
`durasi_jam` = VALUES(`durasi_jam`);

-- Tabel berikut sengaja dibiarkan kosong untuk cabang baru:
--   - m_pelanggan
--   - transaksi
--   - transaksi_detail
--   - pengeluaran
