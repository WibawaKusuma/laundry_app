<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= isset($company['company_name']) ? $company['company_name'] : 'Laundry App'; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ===== RESET & BASE ===== */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a56db 0%, #06b6d4 100%);
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        /* ===== BACKGROUND DECORATIONS ===== */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            opacity: 0.12;
            pointer-events: none;
        }

        body::before {
            width: clamp(200px, 40vw, 500px);
            height: clamp(200px, 40vw, 500px);
            background: #fff;
            top: -10%;
            right: -10%;
        }

        body::after {
            width: clamp(150px, 30vw, 350px);
            height: clamp(150px, 30vw, 350px);
            background: #fff;
            bottom: -8%;
            left: -8%;
        }

        /* ===== WRAPPER ===== */
        .login-wrapper {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        /* ===== CARD ===== */
        .card-login {
            background: #ffffff;
            border: none;
            border-radius: 24px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.15),
                0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-body-login {
            padding: clamp(1.5rem, 6vw, 2.5rem);
        }

        /* ===== LOGO AREA ===== */
        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            text-align: center;
        }

        .logo-icon {
            width: clamp(64px, 15vw, 88px);
            height: clamp(64px, 15vw, 88px);
            background: linear-gradient(135deg, #1a56db, #06b6d4);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 20px rgba(26, 86, 219, 0.3);
            flex-shrink: 0;
        }

        .logo-icon i {
            font-size: clamp(1.5rem, 4vw, 2rem);
            color: #fff;
        }

        .logo-img {
            width: clamp(64px, 15vw, 88px);
            height: clamp(64px, 15vw, 88px);
            object-fit: contain;
            border-radius: 16px;
            margin-bottom: 1rem;
        }

        .logo-title {
            font-size: clamp(1.1rem, 3.5vw, 1.4rem);
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.25rem;
            line-height: 1.3;
        }

        .logo-subtitle {
            font-size: clamp(0.78rem, 2vw, 0.875rem);
            color: #94a3b8;
            margin: 0;
        }

        /* ===== ALERT ===== */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            color: #dc2626;
            font-size: 0.85rem;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-error i {
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* ===== FORM FIELDS ===== */
        .field-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.4rem;
            display: block;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-field {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            color: #1e293b;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-field::placeholder {
            color: #cbd5e1;
        }

        .form-field:focus {
            background: #ffffff;
            border-color: #1a56db;
            box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.12);
        }

        .form-field:focus + .field-icon,
        .input-wrap:focus-within .field-icon {
            color: #1a56db;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            font-size: 0.9rem;
            line-height: 1;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #475569;
        }

        /* ===== SUBMIT BUTTON ===== */
        .btn-login {
            width: 100%;
            padding: 0.85rem;
            font-family: 'Poppins', sans-serif;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #1a56db, #06b6d4);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 15px rgba(26, 86, 219, 0.35);
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 86, 219, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* ===== FOOTER ===== */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: clamp(0.7rem, 1.8vw, 0.78rem);
            color: rgba(255, 255, 255, 0.7);
        }

        /* ===== RESPONSIVE TWEAKS ===== */

        /* Phone small (< 360px) */
        @media (max-width: 359px) {
            body {
                padding: 0.75rem;
            }
            .card-body-login {
                padding: 1.25rem;
            }
        }

        /* Tablet portrait (600px – 1024px) */
        @media (min-width: 600px) and (max-width: 1024px) {
            .login-wrapper {
                max-width: 420px;
            }
        }

        /* Landscape phone */
        @media (max-height: 500px) and (orientation: landscape) {
            body {
                align-items: flex-start;
                padding: 0.75rem;
            }
            .logo-wrap {
                flex-direction: row;
                gap: 1rem;
                margin-bottom: 1rem;
                text-align: left;
            }
            .logo-icon, .logo-img {
                margin-bottom: 0;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">

        <div class="card-login">
            <div class="card-body-login">

                <!-- Logo & Title -->
                <div class="logo-wrap">
                    <?php if (isset($company['company_logo'])): ?>
                        <img src="<?= base_url($company['company_logo']); ?>" alt="Logo" class="logo-img">
                    <?php else: ?>
                        <div class="logo-icon">
                            <i class="fas fa-soap"></i>
                        </div>
                    <?php endif; ?>
                    <h1 class="logo-title"><?= isset($company['company_name']) ? $company['company_name'] : 'Laundry App'; ?></h1>
                    <p class="logo-subtitle">Masuk ke akun Anda untuk melanjutkan</p>
                </div>

                <!-- Error Alert -->
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= $this->session->flashdata('error') ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="<?= site_url('auth/login') ?>" method="post" novalidate>

                    <div style="margin-bottom: 1rem;">
                        <label class="field-label" for="username">Username</label>
                        <div class="input-wrap">
                            <input
                                type="text"
                                class="form-field"
                                id="username"
                                name="username"
                                placeholder="Masukkan username"
                                required
                                autocomplete="username"
                                autocapitalize="off"
                                autocorrect="off"
                            >
                            <i class="fas fa-user field-icon"></i>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label class="field-label" for="password">Password</label>
                        <div class="input-wrap">
                            <input
                                type="password"
                                class="form-field"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password"
                                style="padding-right: 3rem;"
                            >
                            <i class="fas fa-lock field-icon"></i>
                            <button type="button" class="toggle-password" id="togglePwd" aria-label="Tampilkan password">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="login" value="true" class="btn-login" id="btnLogin">
                        <span>Masuk</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                </form>

            </div>
        </div>

        <!-- Footer -->
        <p class="login-footer">
            &copy; <?= date('Y'); ?> Laundry Management System
        </p>

    </div>

    <script>
        // Toggle show/hide password
        const togglePwd = document.getElementById('togglePwd');
        const pwdInput  = document.getElementById('password');
        const eyeIcon   = document.getElementById('eyeIcon');

        togglePwd.addEventListener('click', function () {
            const isHidden = pwdInput.type === 'password';
            pwdInput.type  = isHidden ? 'text' : 'password';
            eyeIcon.classList.toggle('fa-eye', !isHidden);
            eyeIcon.classList.toggle('fa-eye-slash', isHidden);
        });

        // Loading state when form submitted
        document.querySelector('form').addEventListener('submit', function () {
            const btn = document.getElementById('btnLogin');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Memproses...</span>';
            btn.style.opacity = '0.8';
            btn.disabled = true;
        });
    </script>

</body>

</html>