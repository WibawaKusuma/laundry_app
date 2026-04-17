-- =========================================================
-- Update existing database: add m_tipe and relate it to m_paket_laundry
-- Jalankan file ini pada database yang SUDAH berjalan.
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `m_tipe` (
  `id_tipe` int(11) NOT NULL AUTO_INCREMENT,
  `nama_tipe` varchar(100) NOT NULL,
  PRIMARY KEY (`id_tipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `m_tipe` (`id_tipe`, `nama_tipe`) VALUES
(1, 'Cuci Komplit'),
(2, 'Cuci Lipat'),
(3, 'Setrika')
ON DUPLICATE KEY UPDATE `nama_tipe` = VALUES(`nama_tipe`);

ALTER TABLE `m_paket_laundry`
  ADD COLUMN IF NOT EXISTS `id_tipe` int(11) DEFAULT NULL AFTER `id_kategori`;

UPDATE `m_paket_laundry`
SET `id_tipe` = CASE
  WHEN LOWER(`nama_paket`) LIKE '%komplit%' THEN 1
  WHEN LOWER(`nama_paket`) LIKE '%lipat%' THEN 2
  WHEN LOWER(`nama_paket`) LIKE '%setrika%' THEN 3
  ELSE `id_tipe`
END
WHERE `id_tipe` IS NULL;

SET @fk_paket_tipe_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'm_paket_laundry'
    AND CONSTRAINT_NAME = 'fk_paket_tipe'
);

SET @fk_sql := IF(
  @fk_paket_tipe_exists = 0,
  'ALTER TABLE `m_paket_laundry` ADD CONSTRAINT `fk_paket_tipe` FOREIGN KEY (`id_tipe`) REFERENCES `m_tipe` (`id_tipe`) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
