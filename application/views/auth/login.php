<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= isset($company['company_name']) ? $company['company_name'] : 'Laundry App'; ?>">
    <title>Login - <?= isset($company['company_name']) ? $company['company_name'] : 'Laundry App'; ?></title>
    <link rel="manifest" href="<?= base_url('manifest.json?v=3'); ?>">
    <link rel="icon" type="image/png" href="<?= isset($company['company_logo']) ? base_url($company['company_logo']) : base_url('assets/image/logo.png'); ?>">
    <link rel="apple-touch-icon" href="<?= isset($company['company_logo']) ? base_url($company['company_logo']) : base_url('assets/image/logo.png'); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-login {
            border: none;
            border-radius: 20px;
            /* Lebih membulat sedikit */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .btn-login {
            border-radius: 50px;
            padding: 10px 20px;
            /* Padding disesuaikan biar gak kegedean */
            font-weight: 600;
            font-size: 16px;
            /* Ukuran font pas */
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4);
        }

        .form-floating label {
            padding-left: 1.5rem;
        }

        .input-icon {
            position: absolute;
            top: 18px;
            right: 20px;
            color: #ccc;
            z-index: 10;
        }

        .form-control {
            border-radius: 12px;
            /* Input box lebih rounded */
            border: 1px solid #eee;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: #0d6efd;
            box-shadow: none;
        }

        /* Landscape tablet & mobile landscape */
        @media (orientation: landscape) and (max-height: 600px) {
            body {
                align-items: flex-start;
                padding: 1rem 0;
            }
            .card-login .card-body {
                padding: 1.5rem 2rem !important;
            }
        }

        @media (min-width: 576px) and (max-width: 991px) and (orientation: landscape) {
            .login-col {
                max-width: 65% !important;
                flex: 0 0 65% !important;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-10 col-sm-8 col-md-6 col-lg-5 col-xl-4 login-col">

                <div class="card card-login bg-white">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <?php if (isset($company['company_logo'])): ?>
                                <img src="<?= base_url($company['company_logo']); ?>" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;" class="mb-3">
                            <?php else: ?>
                                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-soap fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <h4 class="fw-bold text-dark mb-1"><?= isset($company['company_name']) ? $company['company_name'] : 'Laundry App'; ?></h4>
                            <p class="text-muted small">Silakan login untuk melanjutkan</p>
                        </div>

                        <?php if ($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 small" role="alert">
                                <i class="fas fa-exclamation-circle me-1"></i> <?= $this->session->flashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?= site_url('auth/login') ?>" method="post">

                            <div class="form-floating mb-3 position-relative">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autocomplete="off">
                                <label for="username">Username</label>
                                <i class="fas fa-user input-icon"></i>
                            </div>

                            <div class="form-floating mb-4 position-relative">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                <label for="password">Password</label>
                                <i class="fas fa-lock input-icon"></i>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" name="login" value="true" class="btn btn-primary btn-login btn-lg">
                                    Login <i class="fas fa-arrow-right ms-2 small"></i>
                                </button>
                            </div>

                        </form>

                        <div class="d-grid mb-3">
                            <button id="install-app-btn" type="button" class="btn btn-outline-primary rounded-pill d-none">
                                <i class="fas fa-download me-2"></i>Install App
                            </button>
                        </div>

                        <div class="text-center text-muted small mt-4" style="font-size: 0.8rem;">
                            &copy; <?= date('Y'); ?> Laundry Management System
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let deferredInstallPrompt = null;
        const installButton = document.getElementById('install-app-btn');

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?= base_url('service-worker.js'); ?>').catch(function(error) {
                    console.warn('Service worker gagal didaftarkan:', error);
                });
            });
        }

        window.addEventListener('beforeinstallprompt', function(event) {
            event.preventDefault();
            deferredInstallPrompt = event;

            if (installButton) {
                installButton.classList.remove('d-none');
            }
        });

        window.addEventListener('appinstalled', function() {
            deferredInstallPrompt = null;

            if (installButton) {
                installButton.classList.add('d-none');
            }
        });

        if (installButton) {
            installButton.addEventListener('click', async function() {
                if (!deferredInstallPrompt) {
                    return;
                }

                deferredInstallPrompt.prompt();
                await deferredInstallPrompt.userChoice;
                deferredInstallPrompt = null;
                installButton.classList.add('d-none');
            });
        }
    </script>

</body>

</html>
