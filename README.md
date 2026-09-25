# Laundry App

Laundry App adalah aplikasi manajemen laundry berbasis CodeIgniter 3 untuk operasional kasir/admin. Aplikasi ini menangani master data pelanggan, paket laundry, transaksi, pembayaran, pengambilan cucian, promo, reward member, laporan, dan dashboard omset.

Dokumen ini adalah panduan utama untuk memahami alur aplikasi, aturan bisnis (promo, reward, poin), struktur kode, database, dan proses deploy. **Aturan bisnis di dokumen ini adalah acuan; kode harus mengikuti dokumen ini.**

---

## Daftar Isi

1. [Stack & Struktur Folder](#stack-aplikasi)
2. [Konfigurasi & Menjalankan Lokal](#konfigurasi-lokal)
3. [Multi Cabang (sites/)](#multi-cabang-sites)
4. [Modul Utama](#modul-utama)
5. [Alur Transaksi](#alur-transaksi)
6. [Promo, Reward Member, dan Poin](#promo-reward-member-dan-poin)
7. [Snapshot Tagihan](#snapshot-tagihan)
8. [Peta Kode Tagihan & Benefit](#peta-kode-tagihan--benefit)
9. [Nota Cetak dan WhatsApp](#nota-cetak-dan-whatsapp)
10. [Query SQL untuk Server (folder query/)](#query-sql-untuk-server-folder-query)
11. [Query Validasi Database](#query-validasi-database)
12. [Git dan Deploy](#git-dan-deploy)
13. [Troubleshooting](#troubleshooting)
14. [Checklist Test Setelah Deploy](#checklist-test-setelah-deploy)
15. [Riwayat Perubahan Penting](#riwayat-perubahan-penting)

---

## Stack Aplikasi

- Backend: PHP dengan CodeIgniter 3.
- Database: MySQL/MariaDB (lokal: MariaDB 10.4 dari XAMPP).
- Server lokal: XAMPP (Apache lokal di port `8081`).
- Frontend: HTML, CSS, JavaScript, Bootstrap, FontAwesome, SweetAlert, dan asset lokal di folder `assets`.
- Deploy server: Git + konfigurasi `.cpanel.yml`.

URL lokal:

```text
http://localhost:8081/laundry-app
```

## Struktur Folder Penting

```text
laundry-app/
├── application/
│   ├── config/          Konfigurasi CodeIgniter dan database (diabaikan Git).
│   ├── controllers/     Controller: menerima request dan mengatur alur halaman.
│   ├── core/            MY_Controller (memuat config perusahaan & promo dari tabel config).
│   ├── libraries/       Aturan laundry, kalkulator benefit, builder notifikasi WA.
│   ├── models/          Query database dan logic data.
│   └── views/           Tampilan halaman aplikasi.
├── assets/              CSS, JS, gambar, vendor frontend.
├── sites/               Konfigurasi per cabang/host (diabaikan Git).
├── system/              Core CodeIgniter.
├── query/               File SQL yang harus dijalankan di server saat deploy (ikut Git).
├── database/            Backup & SQL lokal/manual. Tidak ikut Git.
├── index.php            Entry point aplikasi (memilih cabang dari sites/).
├── .htaccess            Rewrite URL.
├── .cpanel.yml          Konfigurasi deploy ke server cPanel.
└── README.md            Dokumentasi proyek ini.
```

## Konfigurasi Lokal

Lokasi project lokal:

```text
C:\xampp\htdocs\laundry-app
```

Database lokal utama: `laundry_app`.

File konfigurasi database: `application/config/database.php`. Nilainya diambil dari konstanta yang diisi `index.php` berdasarkan file di `sites/`:

```php
SITE_DB_HOST, SITE_DB_USER, SITE_DB_PASS, SITE_DB_NAME,
SITE_DB_DRIVER, SITE_DB_CHARSET, SITE_DB_COLLATION, SITE_BASE_URL
```

File `application/config/config.php`, `application/config/database.php`, dan folder `sites/` diabaikan oleh Git agar konfigurasi lokal/server tidak tertimpa.

## Cara Menjalankan di Lokal

1. Jalankan XAMPP, aktifkan Apache dan MySQL.
2. Pastikan database `laundry_app` ada di phpMyAdmin.
3. Pastikan semua file di folder `query/` sudah dijalankan (lihat [Query SQL untuk Server](#query-sql-untuk-server-folder-query)).
4. Buka `http://localhost:8081/laundry-app`.

## Multi Cabang (sites/)

`index.php` memilih konfigurasi cabang berdasarkan host (`sites/hosts.php`):

| Host | Site | Database |
|---|---|---|
| `localhost`, `127.0.0.1`, `192.168.1.12` | `local` | `laundry_app` |
| `darmasaba.islaundry.id` | `darmasaba` | `islaundry_darmasaba` |
| `tabanan.islaundry.id` | `tabanan` | `islaundry_tabanan` |

**Penting:** setiap cabang punya database sendiri. File SQL di folder `query/` harus dijalankan di **setiap database cabang** yang aktif.

Status per 25/09/2026: yang beroperasi baru **Darmasaba** (satu database). Konfigurasi Tabanan sudah disiapkan di `sites/`, tetapi databasenya belum dipakai. Saat Tabanan mulai dipakai, jalankan **semua** file `query/` (urut nomor) di database Tabanan sebelum situsnya dibuka.

Runner migrasi otomatis multi-cabang (`php index.php migrate ...`) **belum ada di kode** (`application/config/migration.php` masih `migration_enabled = FALSE`). Sampai runner dibuat, migrasi dijalankan manual per database lewat phpMyAdmin.

---

## Modul Utama

### Dashboard

Ringkasan operasional, omset, transaksi, dan data penting lain untuk monitoring.

### Pelanggan

Data pelanggan/member beserta saldo poin member. Daftar pelanggan memakai pagination. Chip poin berwarna hijau (ikon hadiah) jika poin sudah mencapai batas reward.

Input **Jenis Pelanggan**: `Umum` (default) atau `Mitra` (villa / kerja sama). Lihat [Pelanggan Mitra](#pelanggan-mitra-villa--kerja-sama).

### Paket Laundry

Master paket layanan: kategori (`Outlet`, `Antar Jemput`, `Satuan Khusus`), tipe (`Cuci Komplit`, `Cuci Lipat`, `Setrika`), satuan (`kg`, `pcs`, `pasang`, ...), dan harga.

Nama paket/tipe dipakai oleh aturan promo & poin (lihat [Kelayakan Layanan](#kelayakan-layanan)). Mengganti nama tipe/paket bisa mengubah siapa yang dapat promo/poin.

### Transaksi

Modul utama: membuat transaksi, menambah/mengedit/membatalkan item, membatalkan transaksi, pembayaran, cetak nota, format nota WhatsApp, menandai selesai/diambil.

Filter daftar transaksi: Semua, Sudah bayar, Belum bayar (default `Belum bayar`).

### Pengaturan Promo

Menu admin untuk mengatur promo dan reward member tanpa mengubah kode:

- Promo Opening (ON/OFF, jumlah gratis kg, label)
- Promo Daily (ON/OFF, minimal kg, gratis kg, label)
- Promo Sepatu (ON/OFF, minimal pasang, gratis pasang, label)
- Reward Member (batas poin, gratis kg). Reward member selalu aktif.

Perubahan setting **hanya berlaku untuk nota yang dibuat setelah disimpan**.

### Laporan

Laporan membaca nilai tagihan dari snapshot transaksi (`subtotal_normal`, `diskon_total`, `total_akhir`), bukan menghitung ulang dari detail.

### Karyawan

Mengelola data karyawan dan akses terkait operasional.

---

## Alur Transaksi

Prinsip utama: **angka di nota WhatsApp adalah kontrak.** Setelah nota dibuat, tagihan hanya boleh berubah jika isi nota diubah (tambah/edit/batal item), dan setiap perubahan harus diikuti kirim ulang nota WhatsApp.

### 1. Buat Transaksi (`Transaksi::simpan`)

Kasir memilih pelanggan, layanan, qty/berat, lalu menyimpan. Sistem:

1. Menolak keranjang yang mencampur sepatu/sandal dengan cucian (lihat [Nota Sepatu Terpisah](#nota-sepatu-terpisah)).
2. **Mengunci jenis pelanggan** (`umum`/`mitra`) ke kolom `transaksi.jenis_pelanggan`. Nota mitra tidak mendapat aturan promo maupun reward (langkah 3 dan 4 dilewati).
3. **Mengunci aturan promo** yang aktif saat itu ke kolom `aturan_promo_*` nota:
   - Nota sepatu/sandal → Promo Sepatu (jika aktif).
   - Nota cucian → Promo Opening jika aktif, kalau tidak Promo Daily jika aktif.
   - Aturan dikunci berdasarkan **status promo saat nota dibuat**, bukan isi keranjang. Nota yang awalnya berisi setrika lalu ditambah cucian kg tetap mendapat promo yang aktif saat nota dibuat.
4. **Memutuskan reward member (satu kali saja):** jika poin pelanggan `>= batas poin` dan nota berisi Cuci Komplit Reguler/Satu Hari (kg), poin langsung dikurangi sebesar batas poin (**reserve**) secara atomik, dan reward dikunci di nota (`reward_poin_dipakai`, `aturan_reward_gratis_qty`).
5. Menghitung dan menyimpan **snapshot tagihan**.
6. Membuat link nota WhatsApp dari snapshot.

### 2. Tambah / Edit / Batal Item

Hanya saat status `Baru` dan belum dibayar. Setelah item berubah, snapshot dihitung ulang **dari data yang terkunci di nota**:

- Aturan promo tetap `aturan_promo_*` nota, bukan setting promo terbaru.
- Reward yang sudah di-reserve tetap dipakai, jatah gratisnya menyesuaikan item (maksimal `aturan_reward_gratis_qty`).
- Jika semua item Cuci Komplit Reguler/Satu Hari dihapus, reward **dilepas** dan poin dikembalikan (**release**), lalu nota memakai promo yang terkunci.
- Reward **tidak pernah ditambahkan** ke nota yang sudah ada, walaupun poin pelanggan mencapai batas setelah nota dibuat.
- Menambah sepatu ke nota cucian (atau sebaliknya) ditolak.

Setelah perubahan, kasir wajib mengirim ulang nota WhatsApp (tombol WA di detail transaksi).

### 3. Pembayaran (`Transaksi::bayar_tagihan`)

Pembayaran **tidak menghitung benefit baru dan tidak mengubah poin reward.** Sistem hanya:

1. Menghitung ulang tagihan dari data terkunci dan membandingkannya dengan snapshot tersimpan. Jika berbeda, pembayaran **diblokir** dan halaman detail menampilkan peringatan (tanda data rusak, perlu dicek admin).
2. Membandingkan dengan `expected_total` yang dikirim form bayar (total yang dilihat kasir). Jika berbeda (misalnya nota diubah di tab lain), pembayaran **ditolak** dan kasir diminta memuat ulang halaman.
3. Mencatat lunas dengan guard `WHERE dibayar='Belum Dibayar' AND total_akhir=<total tervalidasi>`.
4. Memberi poin +1 jika nota sudah selesai dan layak dapat poin.

### 4. Selesai & Pengambilan

- Poin +1 diberikan sekali per nota ketika nota **Selesai/Diambil dan Lunas** (urutan bebas: bayar dulu atau selesai dulu).
- Setelah selesai dan lunas, nota bisa ditandai diambil. Nota pengambilan WA membaca snapshot.

### 5. Batal Transaksi

Transaksi bisa dibatalkan penuh jika status masih `Baru` dan belum dibayar (customer batal, salah input, tidak jadi diproses). Informasi batal: waktu, user, alasan, status `Dibatalkan`. Jika nota memakai reward, **poin dikembalikan otomatis**. Transaksi batal tidak dihitung sebagai omset.

---

## Promo, Reward Member, dan Poin

### Ringkasan

| Benefit | Status diatur | Level | Syarat | Potongan |
|---|---|---|---|---|
| **Reward Member** | Selalu aktif (batas poin & gratis kg di Pengaturan) | Per nota | Poin `>= batas` **saat nota dibuat** + ada Cuci Komplit Reguler/Satu Hari (kg) | Gratis maksimal `gratis kg` (default 3 kg) dari item termurah yang memenuhi syarat. Sisa jatah hangus jika cucian lebih sedikit. |
| **Promo Opening** | Pengaturan (ON/OFF) | Per nota, otomatis | Total Cuci Komplit kg **lebih dari** jumlah gratis | Gratis `gratis kg` (default 3 kg) dari item termurah. Tanpa pembulatan, tanpa kelipatan. |
| **Promo Daily** | Pengaturan (ON/OFF) | Per nota, otomatis | Total Cuci Komplit kg `>=` minimal (default 7 kg) | `floor(total / minimal) x gratis kg` (default 1 kg per 7 kg) dari item termurah. |
| **Promo Sepatu** | Pengaturan (ON/OFF) | Per nota sepatu | Total sepatu (satuan pasang, kategori Satuan Khusus, nama paket mengandung "Sepatu") `>=` minimal (default 5 pasang) | `floor(total / minimal) x gratis pasang` dari sepatu termurah. |

### Prioritas Benefit (satu nota = satu benefit)

1. Reward Member
2. Promo Opening
3. Promo Daily
4. Promo Sepatu

Benefit **tidak pernah digabung**. Jika reward member berlaku, promo di nota itu hangus, walaupun nilai promonya lebih besar. Contoh: 35 kg dengan poin 8 → reward 3 kg (Rp21.000), promo daily 5 kg (Rp35.000) tidak dipakai. Ini keputusan owner.

Promo Opening dan Promo Daily tidak boleh aktif bersamaan (dicegah di halaman Pengaturan). Promo Sepatu boleh aktif bersamaan dengan keduanya karena nota sepatu selalu terpisah.

### Contoh Perhitungan

| Kasus | Hasil |
|---|---|
| Promo Daily, 35 kg Reguler @7.000 | Gratis 5 kg → potongan Rp35.000 |
| Promo Daily, 6,9 kg | Tidak dapat promo |
| Promo Daily, Express 1 kg @12.000 + Reguler 9,6 kg @7.000 | Gratis 1 kg dari yang termurah → Rp7.000 |
| Promo Opening (3 kg), 3 kg | Tidak dapat (harus lebih dari 3 kg) |
| Promo Opening (3 kg), 3,5 kg @7.000 | Gratis 3 kg → bayar 0,5 kg = Rp3.500 |
| Reward (3 kg), Reguler 2,6 kg | Gratis 2,6 kg, sisa 0,4 kg hangus, poin tetap berkurang 8 |
| Reward, Satu Hari 2 kg @10.000 + Reguler 2 kg @7.000 | Gratis 2 kg Reguler + 1 kg Satu Hari = Rp24.000 |
| Poin 8, nota hanya Express/Cuci Lipat | Reward tidak dipakai, poin tetap tersimpan untuk nota berikutnya |

### Poin Member

- **Dapat +1 poin per nota** yang **Selesai/Diambil dan Lunas**, jika nota berisi minimal satu item bertipe **Cuci Komplit atau Cuci Lipat** (kg maupun satuan seperti bed cover, boneka, dll., termasuk Express).
- **Tidak dapat poin:** nota yang hanya berisi Setrika, nota sepatu/sandal, dan nota yang memakai reward member.
- Nota yang mendapat Promo Opening/Daily **tetap dapat poin**.
- **Poin tidak dibatasi.** Poin bisa lebih dari batas (9, 10, ...). Saat reward dipakai, poin dikurangi sebesar batas dan sisanya tetap tersimpan (contoh: 9 → 1).
- Aturan poin baru berlaku untuk nota yang lunas setelah update 25/09/2026. Tidak ada pemberian poin mundur.

### Reward Member: Kapan Dipakai

Reward **hanya diputuskan saat nota dibuat.** Contoh poin 7:

1. Senin nota A dibuat (poin 7) → tanpa reward.
2. Selasa nota B dibuat (poin 7) → tanpa reward.
3. Kamis A dan B dibayar sesuai nota WA masing-masing → poin menjadi 9.
4. Nota berikutnya (C) yang berisi Cuci Komplit Reguler/Satu Hari otomatis memakai reward → poin 9 − 8 = 1.

Karena pemotongan poin dilakukan dengan `UPDATE ... SET poin = poin − batas WHERE poin >= batas`, dua nota tidak mungkin memakai poin yang sama walaupun dibuat bersamaan.

### Kelayakan Layanan

Semua aturan kelayakan ada di `application/libraries/Laundry_service_rules.php`:

| Fungsi | Dipakai untuk | Aturan |
|---|---|---|
| `is_reward_member_service_eligible()` | Reward member | Satuan kg + tipe/paket mengandung "cuci komplit" + nama paket mengandung "reguler" atau "satu hari" (bukan "express") |
| `is_promo_gratis_service_eligible()` | Promo Opening & Daily | Satuan kg + tipe/paket mengandung "cuci komplit" |
| `is_promo_sepatu_service_eligible()` | Promo Sepatu | Satuan pasang + kategori "satuan khusus" + nama paket mengandung "sepatu" |
| `is_item_dapat_poin()` | Poin +1 | Tipe mengandung "cuci komplit" atau "cuci lipat", bukan alas kaki |
| `is_item_alas_kaki()` | Nota terpisah & tanpa poin | Satuan pasang, atau nama paket mengandung "sepatu" (termasuk Sandal) |

### Pelanggan Mitra (Villa / Kerja Sama)

Pelanggan dengan **Jenis Pelanggan = Mitra** (diatur di form tambah/edit pelanggan):

- **Tidak mendapat promo apa pun** (Opening, Daily, Sepatu) dan **tidak memakai reward member**, walaupun poinnya sudah mencapai batas.
- **Tidak mendapat poin** saat nota selesai & lunas.
- Nota WhatsApp mitra tanpa bagian diskon dan poin member.
- Pembayaran per nota seperti pelanggan umum.
- Harga khusus villa dibuat sebagai **paket tersendiri** di master paket. Paket villa saat ini **tidak dikunci**: bisa dipilih untuk pelanggan mana pun, jadi kasir yang memilih paket yang benar. Promo/poin tetap ditentukan oleh jenis pelanggan, bukan oleh paket.
- Jenis pelanggan **dikunci per nota** (`transaksi.jenis_pelanggan`). Mengubah pelanggan dari Mitra ke Umum (atau sebaliknya) hanya berlaku untuk nota yang dibuat setelahnya.
- Saldo poin pelanggan yang diubah menjadi Mitra **dibekukan**, tidak dihapus: tidak bertambah dan tidak dipakai selama Mitra, lalu aktif lagi jika dikembalikan ke Umum.

Pengecekan mitra ada di `Laundry_benefit_calculator::is_nota_mitra()` (tanpa benefit), `Transaksi::simpan()` (tanpa aturan promo & reserve reward), `Transaksi::sinkron_poin_member()` dan `Transaksi_model::sync_member_point()` (tanpa poin).

### Nota Sepatu Terpisah

Owner menetapkan nota sepatu/sandal selalu terpisah dari cucian. Sistem menolak dengan notifikasi:

- Menambah sepatu/sandal ke keranjang yang berisi cucian (dan sebaliknya).
- Menyimpan keranjang campuran.
- Menambah item sepatu ke nota cucian (dan sebaliknya) dari halaman detail.

### Promo Opening: Alur Pakai

Promo Opening dipakai untuk masa promosi (misalnya 3 hari) untuk menarik pelanggan:

1. Aktifkan Promo Opening, **matikan Promo Daily** (wajib, sistem menolak jika keduanya aktif).
2. Selama ON, semua nota cucian baru otomatis mendapat gratis sesuai setting jika total Cuci Komplit kg lebih dari jumlah gratis.
3. Setelah masa promo selesai, matikan Promo Opening dan aktifkan kembali Promo Daily.
4. Nota yang dibuat selama masa opening tetap memakai Promo Opening sampai lunas.

Catatan data lama: nota id 1–15 (April 2026) memakai format Promo Opening lama per item (qty item sudah dipotong, info di `transaksi_detail.keterangan` berformat `free_3kg`). Data ini tetap ditampilkan apa adanya.

---

## Snapshot Tagihan

Snapshot adalah salinan angka tagihan yang disimpan langsung di tabel `transaksi`.

Kolom snapshot:

```text
subtotal_normal        total harga item aktif (qty x harga)
diskon_total           total potongan (reward ATAU promo)
total_akhir            yang harus dibayar = subtotal_normal - diskon_total
benefit_tipe           reward_member | promo_opening | promo_daily | promo_sepatu | NULL
benefit_keterangan     teks penjelasan benefit
reward_member_dipakai, reward_gratis_qty, reward_potongan
promo_gratis_dipakai, promo_gratis_qty, promo_gratis_potongan, promo_gratis_keterangan
snapshot_tagihan_at    kapan snapshot terakhir dihitung
```

Kolom yang dikunci saat nota dibuat:

```text
aturan_promo_tipe, aturan_promo_min_qty, aturan_promo_gratis_qty, aturan_promo_unit, aturan_promo_label
reward_poin_dipakai        poin yang di-reserve untuk reward (0 = tanpa reward)
aturan_reward_gratis_qty   jatah gratis kg reward
```

Aturan menulis snapshot:

| Aksi | Menulis snapshot? |
|---|---|
| Simpan nota, tambah/edit/batal item | Ya (hanya nota belum lunas & tidak batal) |
| Bayar | Ya, salinan final (setelah divalidasi sama dengan snapshot sebelumnya) |
| Buka detail, cetak nota, laporan | **Tidak pernah** |

Model `update_billing_snapshot()` juga menolak menulis snapshot untuk nota yang sudah lunas atau dibatalkan.

### Histori Poin (`poin_member_log`)

Setiap perubahan `m_pelanggan.poin_member` dicatat:

| jenis | delta | Kapan |
|---|---|---|
| `saldo_awal` | saldo lama | Sekali, saat histori mulai dicatat (query `003`) |
| `earn` | +1 | Nota selesai & lunas |
| `reserve` | −batas | Reward dipakai saat nota dibuat |
| `release` | +batas | Nota reward dibatalkan / item reward dihapus |
| `koreksi` | ± | Perbaikan data manual lewat SQL |

Aturan emas: `SUM(delta)` per pelanggan **harus sama** dengan `poin_member`. Dicek oleh `query/900_validasi_reward_poin.sql`.

---

## Peta Kode Tagihan & Benefit

| File | Isi |
|---|---|
| `application/libraries/Laundry_benefit_calculator.php` | **Satu-satunya tempat perhitungan benefit.** `hitung()` untuk nota terbuka (dari data terkunci), `ringkasan_dari_snapshot()` untuk nota lunas/batal, `snapshot_fields()` untuk kolom yang disimpan. |
| `application/libraries/Laundry_service_rules.php` | Aturan kelayakan layanan (reward, promo, poin, alas kaki) dan format item. |
| `application/libraries/Laundry_notification_builder.php` | Teks nota WhatsApp (diskon, poin, pembayaran). |
| `application/controllers/Transaksi.php` | Alur nota: `simpan`, `tambah_item`, `update_detail_item`, `batal_detail_item`, `batalkan`, `bayar_tagihan`, `update_status`, `tandai_diambil`, `detail`, `cetak`. Helper penting: `siapkan_aturan_promo_order`, `simpan_snapshot_tagihan`, `hitung_ringkasan_transaksi`, `sinkron_poin_member`. |
| `application/models/Transaksi_model.php` | `create_transaction_with_details` (reserve poin atomik), `release_reward_reservation`, `sync_member_point` (earn), `record_payment`, `update_billing_snapshot`, `cancel_transaction` (release otomatis), query laporan berbasis snapshot. |
| `application/controllers/Pengaturan.php` + `views/pengaturan/promo.php` | Setting promo & reward member (tabel `config`). |

Aturan untuk develop berikutnya:

1. Perubahan aturan promo/reward **hanya** di `Laundry_benefit_calculator` dan `Laundry_service_rules`. Jangan menghitung potongan di controller, view, atau builder WA.
2. Jangan menulis snapshot di halaman baca (detail, cetak, laporan).
3. Jangan mengubah `m_pelanggan.poin_member` tanpa mencatat ke `poin_member_log` di DB transaction yang sama.
4. Aturan baru yang tergantung setting harus dikunci ke kolom transaksi saat nota dibuat, supaya nota lama tidak berubah.
5. Perubahan struktur/data database harus dibuat sebagai file SQL idempotent di folder `query/`.

---

## Nota Cetak dan WhatsApp

Nota cetak dan WhatsApp membaca angka dari snapshot/kalkulator yang sama dengan halaman detail, sehingga ketiganya selalu sama.

Informasi yang ditampilkan:

- Paket yang diambil dan subtotal per item.
- Total sebelum diskon, diskon (reward atau promo), total yang harus dibayar. Jika tidak ada diskon, cukup "Total tagihan".
- Poin member:
  - Nota memakai reward: "Reward member dipakai di nota ini (8 poin)" + sisa poin.
  - Poin sudah masuk: "+1" + total poin.
  - Poin `>=` batas: reward otomatis dipakai di nota cucian Cuci Komplit Reguler/Satu Hari berikutnya.
  - Nota layak poin: poin saat ini + keterangan +1 setelah selesai & lunas.
- Status pembayaran dan status pengambilan.

Setiap perubahan item nota → kirim ulang nota WhatsApp dari halaman detail.

---

## Query SQL untuk Server (folder query/)

Folder `query/` berisi SQL yang **harus dijalankan di setiap database cabang** saat deploy. Semua file aman dijalankan ulang (idempotent). Urutan berdasarkan nomor file.

| File | Kapan | Isi |
|---|---|---|
| `001_struktur_reward_poin.sql` | **Sebelum** kode baru di-deploy | Kolom `transaksi.reward_poin_dipakai`, `transaksi.aturan_reward_gratis_qty`, tabel `poin_member_log`, config `reward_member_batas_poin` (8) & `reward_member_gratis_qty` (3), tandai nota reward lama. |
| `002_koreksi_data_nota_reward_20260925.sql` | Setelah 001 | Koreksi 3 nota (Abdul, Kadek adi, Sintia) sesuai nota WA. Dikunci invoice & nominal lama; di cabang lain atau jika data sudah berubah, otomatis dilewati. |
| `003_saldo_awal_poin_member.sql` | Setelah 002 | Mencatat saldo poin lama ke `poin_member_log` sebagai `saldo_awal`. |
| `004_jenis_pelanggan_mitra.sql` | **Sebelum** kode baru di-deploy | Kolom `m_pelanggan.jenis_pelanggan` dan `transaksi.jenis_pelanggan` (default `umum`). |
| `900_validasi_reward_poin.sql` | Setelah deploy & saat rekap bulanan | Cek read-only. Semua hasil `jumlah` harus 0. |

Langkah di server (per database cabang):

1. Backup database (phpMyAdmin → Export).
2. Jalankan `001` dan `004` → deploy kode → jalankan `002` → `003`.
3. Jalankan `900_validasi_reward_poin.sql` dan pastikan semua `jumlah` = 0.
4. Lakukan [Checklist Test](#checklist-test-setelah-deploy).

Folder `database/` (lokal, tidak ikut Git) dipakai untuk backup, contoh: `database/backup_laundry_app_sebelum_fix_20260925.sql`.

File SQL lama yang pernah dibuat (ada di `database/` lokal pemilik):

```text
update_cancel_transaction.sql
update_transaction_billing_snapshot.sql
update_promo_config_final.sql
update_promo_sepatu_config.sql
update_aturan_promo_transaksi.sql
backfill_transaction_billing_snapshot.sql
cleanup_transaction_data.sql   <- HANYA untuk test lokal, JANGAN dijalankan di server
```

## Query Validasi Database

Validasi lengkap ada di `query/900_validasi_reward_poin.sql`. Query cepat:

```sql
-- Transaksi tanpa snapshot
SELECT COUNT(*) AS belum_snapshot
FROM transaksi
WHERE snapshot_tagihan_at IS NULL AND status <> 'Dibatalkan';

-- Ringkasan per status bayar (bandingkan dengan uang fisik)
SELECT dibayar, COUNT(*) AS jumlah, SUM(subtotal_normal) AS subtotal,
       SUM(diskon_total) AS diskon, SUM(total_akhir) AS total_akhir
FROM transaksi
WHERE status <> 'Dibatalkan'
GROUP BY dibayar;

-- Transaksi batal
SELECT id, kode_invoice, status, batal_at, batal_by, alasan_batal
FROM transaksi
WHERE status = 'Dibatalkan'
ORDER BY batal_at DESC;

-- Histori poin satu pelanggan
SELECT created_at, jenis, delta, saldo_setelah, id_transaksi, keterangan
FROM poin_member_log
WHERE id_pelanggan = ?
ORDER BY id;
```

---

## Git dan Deploy

- Folder `database/`, `sites/`, `application/config/config.php`, dan `application/config/database.php` diabaikan Git.
- Folder `query/` **ikut Git** supaya SQL deploy tersimpan bersama kodenya, tetapi **tidak** ikut `.cpanel.yml` (tidak disalin ke folder publik). SQL dijalankan manual di phpMyAdmin.

`.cpanel.yml` melakukan deploy:

```text
index.php
.htaccess
application/
assets/
system/
```

Jika folder `database` terlanjur tracked:

```bash
git rm --cached -r database
git add .gitignore
git commit -m "Remove database files from repository"
git push
```

---

## Troubleshooting

### Aplikasi Tidak Bisa Dibuka

- Apache & MySQL XAMPP aktif. Apache lokal memakai port `8081` (port 80 dipakai aplikasi lain).
- URL: `http://localhost:8081/laundry-app`.
- `.htaccess` tersedia, `base_url` benar atau auto-detect berjalan.

### Error Database

- MySQL aktif, database sesuai `sites/<cabang>.php`, username/password benar.
- Error kolom `reward_poin_dipakai` / tabel `poin_member_log` tidak ada → jalankan `query/001_struktur_reward_poin.sql`.

### Promo Tidak Muncul

- Promo aktif di Pengaturan **saat nota dibuat** (cek kolom `aturan_promo_tipe` nota).
- Qty memenuhi syarat (Opening: lebih dari jumlah gratis; Daily: minimal kg; Sepatu: minimal pasang).
- Jenis layanan sesuai [Kelayakan Layanan](#kelayakan-layanan).
- Pelanggan dengan poin `>=` batas dan item Reguler/Satu Hari mendapat reward, bukan promo (prioritas).

### Reward Tidak Dipakai padahal Poin Sudah 8

- Reward hanya diputuskan saat nota dibuat. Jika poin mencapai 8 setelah nota dibuat, reward dipakai di nota berikutnya.
- Nota harus berisi Cuci Komplit Reguler/Satu Hari satuan kg (Express, Cuci Lipat, Setrika, barang satuan tidak bisa).

### Pembayaran Ditolak "total tagihan sudah berubah"

Nota diubah (di tab/perangkat lain) setelah halaman dibuka. Muat ulang halaman, cek total terbaru, kirim ulang nota WA jika perlu.

### Pembayaran Diblokir "total tersimpan berbeda dengan hitungan sistem"

Snapshot nota tidak cocok dengan hitungan dari data terkunci (data rusak/diubah manual). Jangan ubah manual tanpa backup; cek item, `aturan_promo_*`, dan `reward_poin_dipakai` nota tersebut.

### Laporan Tidak Balance

- Transaksi batal tidak dihitung sebagai omset.
- Semua transaksi punya snapshot.
- Query laporan memakai `total_akhir`, bukan hitung ulang dari detail.
- Jalankan `query/900_validasi_reward_poin.sql`.

### Poin Pelanggan Tidak Sesuai

Lihat histori `poin_member_log` pelanggan tersebut. Jika saldo tidak sama dengan `SUM(delta)`, ada perubahan poin di luar aplikasi.

---

## Checklist Test Setelah Deploy

- [ ] Transaksi cucian di bawah 7 kg → tanpa promo.
- [ ] Transaksi 8 kg → promo daily 1 kg.
- [ ] Transaksi sepatu 5 pasang (nota khusus sepatu) → promo sepatu 1 pasang.
- [ ] Menambah sepatu ke nota cucian → ditolak dengan notifikasi.
- [ ] Pelanggan poin 7 dengan 2 nota terbuka → bayar keduanya, total sesuai nota WA masing-masing, poin jadi 9.
- [ ] Pelanggan poin ≥ 8, nota baru Reguler → reward dipakai saat nota dibuat, poin berkurang 8.
- [ ] Nota reward dibatalkan → poin kembali.
- [ ] Buka detail/cetak nota berkali-kali → total tidak berubah, laporan piutang stabil.
- [ ] Bayar dengan total yang sudah berubah (dua tab) → ditolak.
- [ ] Nota Setrika saja lunas → poin tidak bertambah. Nota Cuci Lipat lunas → poin +1.
- [ ] Pelanggan Mitra (poin ≥ 8, cucian ≥ 7 kg) → tanpa promo, tanpa reward, poin tidak bertambah setelah lunas, nota WA tanpa bagian poin.
- [ ] Cetak nota dan WhatsApp → angka sama dengan detail.
- [ ] Laporan omset/kas/piutang.
- [ ] `query/900_validasi_reward_poin.sql` → semua 0.

---

## Riwayat Perubahan Penting

### 25/09/2026: Jenis pelanggan Mitra (villa)

Kerja sama villa: pelanggan bisa ditandai `Mitra` di form pelanggan. Nota mitra tanpa promo, reward, dan poin. Jenis pelanggan dikunci per nota (`query/004`).

### 25/09/2026: Kunci tagihan, reward, dan poin

Masalah: tagihan nota bisa berbeda dengan nota WhatsApp. Penyebabnya reward member dicek dari poin pelanggan **saat bayar**, sehingga nota yang sudah dikirim berubah ketika poin pelanggan naik dari nota lain. Selain itu, halaman detail/cetak menulis ulang snapshot setiap dibuka. Contoh: Abdul, Kadek adi, Sintia.

Perbaikan:

- Reward diputuskan & poin di-reserve saat nota dibuat. Bayar tidak lagi menghitung benefit.
- Detail/cetak tidak menulis snapshot. Pembayaran divalidasi dengan `expected_total`.
- Satu kalkulator benefit terpusat (`Laundry_benefit_calculator`), potongan dari item termurah.
- Promo Opening per nota & otomatis, tanpa pembulatan, qty item tidak dipotong.
- Aturan promo dikunci berdasarkan status promo saat nota dibuat.
- Poin tidak dibatasi 8. Cuci Lipat & barang satuan dapat poin, setrika saja & sepatu tidak.
- Nota sepatu wajib terpisah.
- Histori poin `poin_member_log`, setting reward di menu Pengaturan.
- Koreksi data 3 nota sesuai nota WhatsApp (`query/002`).
