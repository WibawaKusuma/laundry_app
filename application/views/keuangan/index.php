<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h5 class="">
            <i class="fas fa-money-bill-wave me-2"></i> Ringkasan Kas dan Pengeluaran
        </h5>
    </div>

    <div class="flash-data-success" data-flashdata="<?= $this->session->flashdata('success'); ?>"></div>

    <div class="row mb-4 g-3">
        <?php if (!empty($is_admin)) : ?>
            <div class="col-12 col-md-6 col-xl-6">
                <div class="card shadow-sm rounded-4 finance-summary-card" style="border: 0; border-left: 5px solid #1f297a;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between finance-summary-content">
                            <div class="finance-summary-text">
                                <p class="text-muted fw-bold text-uppercase small mb-1 finance-summary-label">Kas Masuk</p>
                                <h3 class="mb-0" style="color:#1f297a;">
                                    Rp <?= number_format($total_kas_masuk, 0, ',', '.'); ?>
                                </h3>
                            </div>
                            <div class="rounded-3 p-3 d-flex align-items-center justify-content-center finance-summary-icon" style="width: 60px; height: 60px; background: rgba(31,41,122,0.1); color:#1f297a;">
                                <i class="fas fa-wallet fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-6">
                <div class="card shadow-sm rounded-4 finance-summary-card" style="border: 0; border-left: 5px solid #b7791f;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between finance-summary-content">
                            <div class="finance-summary-text">
                                <p class="text-muted fw-bold text-uppercase small mb-1 finance-summary-label">Piutang Periode</p>
                                <h3 class="mb-0" style="color:#b7791f;">
                                    Rp <?= number_format($total_piutang, 0, ',', '.'); ?>
                                </h3>
                            </div>
                            <div class="rounded-3 p-3 d-flex align-items-center justify-content-center finance-summary-icon" style="width: 60px; height: 60px; background: rgba(255,193,7,0.16); color:#b7791f;">
                                <i class="fas fa-file-invoice-dollar fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-6">
                <div class="card shadow-sm rounded-4 finance-summary-card" style="border: 0; border-left: 5px solid #198754;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between finance-summary-content">
                            <div class="finance-summary-text">
                                <p class="text-muted fw-bold text-uppercase small mb-1 finance-summary-label">Saldo Operasional</p>
                                <h3 class="mb-0" style="color:#198754;">
                                    Rp <?= number_format($saldo_operasional, 0, ',', '.'); ?>
                                </h3>
                            </div>
                            <div class="rounded-3 p-3 d-flex align-items-center justify-content-center finance-summary-icon" style="width: 60px; height: 60px; background: rgba(25,135,84,0.1); color:#198754;">
                                <i class="fas fa-scale-balanced fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-12 col-md-6 col-xl-6">
            <div class="card shadow-sm rounded-4 finance-summary-card" style="border: 0; border-left: 5px solid #dc3545;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between finance-summary-content">
                        <div class="finance-summary-text">
                            <p class="text-muted fw-bold text-uppercase small mb-1 finance-summary-label">Total Pengeluaran</p>
                            <h3 class="text-danger mb-0">
                                Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 d-flex align-items-center justify-content-center finance-summary-icon" style="width: 60px; height: 60px;">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-light border rounded-4 mb-4">
        <div class="fw-semibold mb-1">Cara membaca angka di halaman ini</div>
        <?php if (!empty($is_admin)) : ?>
            <small class="text-muted">
                Kas masuk dihitung dari transaksi yang sudah lunas berdasarkan tanggal bayar. Pengeluaran berasal dari data pengeluaran yang Anda input manual. Saldo operasional adalah selisih kas masuk dan pengeluaran pada periode aktif.
            </small>
        <?php else : ?>
            <small class="text-muted">
                Card ini menampilkan total pengeluaran yang sudah dicatat pada periode aktif berdasarkan filter tanggal yang sedang digunakan.
            </small>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-5">

        <div class="card-header app-section-header py-3">
            <div class="row align-items-center g-3">

                <div class="col-12 col-lg me-auto">
                    <h6 class="mb-0 text-danger">
                        <i class="fas fa-history me-2"></i> Riwayat Pengeluaran
                    </h6>
                </div>

                <div class="col-12 col-lg-auto">
                    <form action="" method="get">
                        <div class="row g-2">

                            <div class="col-6 col-lg-auto">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar"></i></span>
                                    <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal; ?>">
                                </div>
                            </div>
                            <div class="col-6 col-lg-auto">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-arrow-right"></i></span>
                                    <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir; ?>">
                                </div>
                            </div>

                            <div class="col-12 col-lg-auto d-grid">
                                <button type="submit" class="btn btn-sm btn-primary px-3">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                            </div>

                            <div class="col-auto d-none d-lg-block border-start mx-2"></div>

                            <div class="col-6 col-lg-auto d-grid">
                                <a href="<?= base_url('keuangan/excel?tgl_awal=' . $tgl_awal . '&tgl_akhir=' . $tgl_akhir); ?>" target="_blank" class="btn btn-success btn-sm px-3">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </a>
                            </div>

                            <div class="col-6 col-lg-auto d-grid">
                                <a href="<?= base_url('keuangan/tambah'); ?>" class="btn btn-primary btn-sm px-3">
                                    <i class="fas fa-plus me-1"></i> Baru
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="ps-3">No</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                            <th class="d-none d-md-table-cell">Catatan</th>
                            <th class="d-none d-md-table-cell">Oleh</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pengeluaran)) : ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-search-dollar fa-3x mb-3 opacity-50"></i>
                                    <p>Tidak ada data pengeluaran pada periode ini.</p>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($pengeluaran as $index => $row) : ?>
                                <tr>
                                    <td class="ps-3"><?= $index + 1 ?></td>
                                    <td><?= date('d/m/y', strtotime($row->tgl_pengeluaran)); ?></td>
                                    <td class="text-dark"><?= $row->keterangan; ?></td>
                                    <td class="text-danger text-nowrap">Rp <?= number_format($row->nominal, 0, ',', '.'); ?></td>

                                    <td class="d-none d-md-table-cell">
                                        <small class="text-muted"><?= $row->catatan; ?></small>
                                    </td>

                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-user-circle me-1"></i> <?= $row->nama_user; ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <a href="<?= base_url('keuangan/edit/' . $row->id); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('keuangan/hapus/' . $row->id); ?>" class="btn btn-sm btn-outline-danger btn-hapus" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const flashData = $('.flash-data-success').data('flashdata');
        if (flashData) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: flashData,
                showConfirmButton: false,
                timer: 1500
            });
        }
        $('.btn-hapus').on('click', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            Swal.fire({
                title: 'Yakin hapus data?',
                text: "Data akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.location.href = href;
                }
            });
        });
    });
</script>
