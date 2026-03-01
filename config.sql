-- =============================================
-- TABEL CONFIG: Menyimpan pengaturan perusahaan
-- =============================================

CREATE TABLE IF NOT EXISTS config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  config_key VARCHAR(50) NOT NULL UNIQUE,
  config_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO config (config_key, config_value) VALUES
('company_name', 'LAUNDRY APP'),
('company_address', 'Jl. Mawar Melati No. 123, Tabanan, Bali'),
('company_phone', '0812-3456-7890'),
('company_tagline', 'Layanan Laundry Kilat'),
('company_logo', 'assets/image/logo.png');
