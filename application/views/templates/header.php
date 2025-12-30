<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Laundry</title>
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/all.min.css') ?>" rel="stylesheet">

    <style>
        /* CSS Tambahan untuk perbaikan UI */
        .sidebar {
            min-height: 100vh;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        /* Jarak agar konten tidak tertutup Navbar Atas */
        body {
            padding-top: 56px;
        }

        /* Perbaikan Z-Index agar Navbar selalu di atas Sidebar */
        .navbar {
            z-index: 1030;
            /* Standar Bootstrap Fixed Top */
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
    </style>
</head>

<body>

    <nav class="navbar navbar-dark bg-primary fixed-top flex-md-nowrap p-2 shadow">

        <button class="navbar-toggler d-md-none collapsed me-2 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand col-md-3 col-lg-2 me-0 ps-2 fs-6" href="<?= base_url('dashboard') ?>">
            <i class="fas fa-soap me-2"></i> LAUNDRY APP
        </a>

    </nav>

    <div class="container-fluid">
        <div class="row">