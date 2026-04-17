<!-- <div class="alert alert-info">
    Halaman saat ini: <strong><?= $this->uri->segment(1); ?></strong>
</div> -->
<!-- <style>
    .nav-link {
        background-color: #000000;
    }
</style> -->
<!-- <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse"> -->
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse shadow-sm">
    <div class="position-sticky pt-3 sidebar-inner">
        <ul class="nav flex-column nav-pills mb-4 sidebar-nav">

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

                <!-- <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Admin Area</span>
                </h6> -->

                <li class="nav-item">
                    <a class="nav-link <?= $uri == 'paket' ? 'active' : '' ?>" href="<?= base_url('paket') ?>">
                        <i class="fas fa-tshirt me-2"></i> Paket
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $uri == 'karyawan' ? 'active' : '' ?>" href="<?= base_url('karyawan') ?>">
                        <i class="fas fa-user-tie me-2"></i> Karyawan
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
                    <a class="nav-link <?= $uri == 'dashboard_omset' ? 'active' : '' ?>" href="<?= base_url('dashboard_omset') ?>">
                        <i class="fas fa-chart-line me-2"></i> Omset
                    </a>
                </li>


            <?php endif; ?>
            <hr class="sidebar-divider">
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?= base_url('auth/logout') ?>">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>

        <div class="px-3 mb-4 mt-auto">
            <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden position-relative">

                <div class="position-absolute top-0 start-0 w-100" style="height: 40px; background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%); opacity: 0.15;"></div>

                <div class="card-body text-center pt-4 pb-3">

                    <div class="position-relative d-inline-block mb-2">
                        <?php
                        $fullname = $this->session->userdata('name') ? $this->session->userdata('name') : '';
                        $username = $this->session->userdata('username') ? $this->session->userdata('username') : 'A';
                        $initial = $fullname ? strtoupper(substr($fullname, 0, 1)) : strtoupper(substr($username, 0, 1));
                        ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                            style="width: 60px; height: 60px; background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%); font-size: 24px;">
                            <?= $initial; ?>
                        </div>
                        <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="width: 15px; height: 15px;"></span>
                    </div>

                    <h6 class="mb-1 fw-bold text-dark text-truncate px-2">
                        <?= $fullname ? ucwords($fullname) : ucfirst($username); ?>
                    </h6>
                    <p class="mb-1 text-muted text-truncate px-2" style="font-size: 12px;">
                        @<?= $username; ?>
                    </p>

                    <div class="d-inline-block">
                        <?php $role = $this->session->userdata('role'); ?>
                        <span class="badge rounded-pill bg-light <?= ($role == 'admin') ? 'text-primary border-primary' : 'text-success border-success'; ?> border bg-opacity-10 px-3 py-1 fw-normal" style="font-size: 11px; letter-spacing: 0.5px;">
                            <?= strtoupper($role); ?>
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</nav>
