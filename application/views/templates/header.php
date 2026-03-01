<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($company['company_name']) ? $company['company_name'] : 'Sistem Laundry'; ?></title>
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/all.min.css') ?>" rel="stylesheet">

    <link href="<?= base_url('assets/css/fonts.css') ?>" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        /* CSS Tambahan untuk perbaikan UI */
        .sidebar {
            min-height: 100vh;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        /* Jarak agar konten tidak tertutup Navbar Atas */
        body {
            padding-top: 56px;
            font-family: 'Poppins', sans-serif;
        }

        /* Perbaikan Z-Index agar Navbar selalu di atas Sidebar */
        .navbar {
            z-index: 1030;
            /* Standar Bootstrap Fixed Top */
        }

        p,
        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Agar tulisan Laundry App terlihat rapi di mobile */
        @media (max-width: 767.98px) {
            .navbar-brand {
                font-size: 1rem;
                /* Kecilkan sedikit font di HP */
                width: auto;
                /* Jangan full width */
            }
        }

        @media (max-width: 767.98px) {
            #sidebarMenu {
                position: fixed;
                /* Membuat sidebar mengambang */
                top: 56px;
                /* Muncul tepat di bawah Navbar (sesuai tinggi navbar) */
                bottom: 0;
                /* Memanjang sampai bawah layar */
                left: 0;
                z-index: 100;
                /* Agar berada di atas konten utama */
                width: 250px;
                /* Lebar sidebar di HP */
                padding-top: 20px;
                overflow-y: auto;
                /* Agar bisa discroll jika menu panjang */
                background-color: #ffffff;
                /* Pastikan background putih/terang */
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
                /* Bayangan agar terlihat terpisah */
                transition: all 0.3s ease-in-out;
            }
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.3);
            /* Warna Hitam Transparan */
            backdrop-filter: blur(4px);
            /* INI KUNCINYA: Membuat Blur */
            z-index: 99;
            /* Di bawah Sidebar (100) tapi di atas Konten */
            display: none;
            /* Default sembunyi */
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        /* Class ini akan ditambahkan oleh Javascript nanti */
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark bg-primary fixed-top flex-md-nowrap p-2 shadow-lg">

        <button class="navbar-toggler d-md-none collapsed me-2 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand col-md-3 col-lg-2 me-0 ps-2 fs-6" href="<?= base_url('dashboard') ?>">
            <i class="fas fa-soap me-2"></i> <?= isset($company['company_name']) ? $company['company_name'] : 'LAUNDRY APP'; ?>
        </a>

    </nav>

    <div class="container-fluid">
        <div class="row">