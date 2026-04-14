# Multi-Site Setup

Project ini sekarang mendukung banyak cabang dengan satu source code yang sama.

## Struktur

- `sites/hosts.php`: mapping host ke key site
- `sites/<site>.php`: config per cabang
- `database/schema.sql`: struktur database cabang baru
- `database/seed_master.sql`: seed master awal

## Cara Menambah Cabang Baru

1. Buat database baru, contoh `gianyar_islaundry`.
2. Import `database/schema.sql`.
3. Import `database/seed_master.sql`.
4. Buat file config baru, contoh `sites/gianyar.php`.
5. Tambahkan host di `sites/hosts.php`.
6. Arahkan subdomain ke source code laundry yang sama.

## Contoh SQL

```sql
CREATE DATABASE gianyar_islaundry CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE gianyar_islaundry;
SOURCE database/schema.sql;
SOURCE database/seed_master.sql;
```

## Contoh File `sites/gianyar.php`

```php
<?php

return [
    'site_key' => 'gianyar',
    'site_name' => 'ISLaundry Gianyar',
    'base_url' => 'https://gianyar.islaundry.id/',
    'db_hostname' => 'localhost',
    'db_username' => 'root',
    'db_password' => '',
    'db_database' => 'gianyar_islaundry',
    'db_driver' => 'mysqli',
    'db_charset' => 'utf8',
    'db_collation' => 'utf8_general_ci',
    'upload_dir' => 'uploads/gianyar',
];
```

## Catatan Operasional

- `islaundry.id` sebaiknya dipisah sebagai landing page / company profile.
- Subdomain cabang diarahkan ke source code app laundry ini.
- Host yang tidak terdaftar akan ditolak dengan `503`, supaya tidak salah masuk ke database cabang lain.
- `database/schema.sql` hanya membuat tabel, index, dan foreign key.
- `database/seed_master.sql` hanya mengisi data master awal.
- Tabel yang tetap kosong pada cabang baru:
  - `m_pelanggan`
  - `transaksi`
  - `transaksi_detail`
  - `pengeluaran`
- Data master yang ikut dibawa:
  - `config`
  - `m_users`
  - `m_metode_bayar`
  - `m_kategori`
  - `m_satuan`
  - `m_paket_laundry`
