<!-- <div class="alert alert-info">
    Halaman saat ini: <strong><?= $this->uri->segment(1); ?></strong>
</div> -->

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column nav-pills">

            <?php $uri = $this->uri->segment(1); ?>

            <li class="nav-item">
                <a class="nav-link <?= ($uri == '' || $uri == 'dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                    <i class="fas fa-home me-2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $uri == 'pelanggan' ? 'active' : '' ?>" href="<?= base_url('pelanggan') ?>">
                    <i class="fas fa-users me-2"></i> Pelanggan
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $uri == 'transaksi' ? 'active' : '' ?>" href="<?= base_url('transaksi') ?>">
                    <i class="fas fa-cart-plus me-2"></i> Transaksi
                </a>
            </li>

            <?php if ($this->session->userdata('role') === 'admin') : ?>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Admin Area</span>
                </h6>

                <li class="nav-item">
                    <a class="nav-link <?= $uri == 'paket' ? 'active' : '' ?>" href="<?= base_url('paket') ?>">
                        <i class="fas fa-tshirt me-2"></i> Paket Laundry
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $uri == 'laporan' ? 'active' : '' ?>" href="<?= base_url('laporan') ?>">
                        <i class="fas fa-file-alt me-2"></i> Laporan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $uri == 'keuangan' ? 'active' : '' ?>" href="<?= base_url('keuangan') ?>">
                        <i class="fas fa-wallet me-2"></i> Keuangan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $uri == 'karyawan' ? 'active' : '' ?>" href="<?= base_url('karyawan') ?>">
                        <i class="fas fa-user-tie me-2"></i> Data Karyawan
                    </a>
                </li>

            <?php endif; ?>
            <hr>
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?= base_url('auth/logout') ?>">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>