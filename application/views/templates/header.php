<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= isset($company['company_name']) ? $company['company_name'] : 'Laundry App'; ?>">
    <title><?= isset($company['company_name']) ? $company['company_name'] : 'Sistem Laundry'; ?></title>
    <link rel="icon" type="image/png" href="<?= isset($company['company_logo']) ? base_url($company['company_logo']) : base_url('assets/image/logo.png'); ?>">
    <link rel="manifest" href="<?= base_url('manifest.json?v=3'); ?>">
    <link rel="apple-touch-icon" href="<?= isset($company['company_logo']) ? base_url($company['company_logo']) : base_url('assets/image/logo.png'); ?>">
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/all.min.css') ?>" rel="stylesheet">

    <link href="<?= base_url('assets/css/fonts.css') ?>" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="<?= base_url('assets/css/custom-ui.css?v=2') ?>" rel="stylesheet">

<body>

    <nav class="navbar navbar-dark bg-primary fixed-top flex-md-nowrap p-2 shadow-lg">

        <button class="navbar-toggler d-md-none collapsed me-2 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand col-md-3 col-lg-2 me-0 ps-2 fs-6" href="<?= base_url('dashboard') ?>">
            <i class="fas fa-soap me-2"></i> <?= isset($company['company_name']) ? $company['company_name'] : 'LAUNDRY APP'; ?>
        </a>

        <button id="install-app-btn" class="btn btn-light btn-sm ms-auto me-2 d-none" type="button">
            <i class="fas fa-download me-1"></i> Install App
        </button>

    </nav>

    <div class="container-fluid">
        <div class="row">
