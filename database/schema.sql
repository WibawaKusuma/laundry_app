-- =========================================================
-- ISLaundry Multi-Branch Schema
-- =========================================================
-- Gunakan file ini untuk membuat database cabang baru.
-- File ini HANYA membuat struktur tabel, index, dan foreign key.
-- File ini TIDAK mengisi data transaksi, transaksi_detail, atau pengeluaran.
-- Contoh:
--   CREATE DATABASE badung_islaundry CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
--   USE badung_islaundry;
--   SOURCE database/schema.sql;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(50) NOT NULL,
  `config_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `m_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'kasir',
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `username_2` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `m_pelanggan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_hp` (`no_hp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `m_metode_bayar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `m_kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `m_satuan` (
  `id_satuan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_satuan` varchar(50) NOT NULL,
  PRIMARY KEY (`id_satuan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `m_paket_laundry` (
  `id_paket_laundry` int(11) NOT NULL AUTO_INCREMENT,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_paket` varchar(255) DEFAULT NULL,
  `id_satuan` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `durasi_jam` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_paket_laundry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_invoice` varchar(20) DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `tgl_masuk` datetime DEFAULT NULL,
  `batas_waktu` datetime DEFAULT NULL,
  `tgl_bayar` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `dibayar` varchar(20) DEFAULT NULL,
  `id_metode_bayar` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transaksi_id_metode_bayar` (`id_metode_bayar`),
  KEY `idx_transaksi_id_pelanggan` (`id_pelanggan`),
  KEY `idx_transaksi_id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `transaksi_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_paket` int(11) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `harga` int(11) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_transaksi` (`id_transaksi`),
  KEY `idx_detail_transaksi_id_paket` (`id_paket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `pengeluaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tgl_pengeluaran` date NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nominal` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pengeluaran_id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_m_metode_bayar`
    FOREIGN KEY (`id_metode_bayar`) REFERENCES `m_metode_bayar` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_m_pelanggan`
    FOREIGN KEY (`id_pelanggan`) REFERENCES `m_pelanggan` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_users`
    FOREIGN KEY (`id_user`) REFERENCES `m_users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `fk_transaksi_detail_transaksi`
    FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_transaksi_m_paket_laundry`
    FOREIGN KEY (`id_paket`) REFERENCES `m_paket_laundry` (`id_paket_laundry`)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `fk_pengeluaran_users`
    FOREIGN KEY (`id_user`) REFERENCES `m_users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
