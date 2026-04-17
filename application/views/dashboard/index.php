<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="row align-items-center pt-3 pb-2 mb-4 border-bottom">

        <div class="col-12 col-md mb-3 mb-md-0">
            <h5 class="mb-0">Dashboard Overview</h5>
            <small class="text-muted text-nowrap">
                Periode: <?= date('d M Y', strtotime($tgl_awal)); ?> s/d <?= date('d M Y', strtotime($tgl_akhir)); ?>
            </small>
        </div>

        <div class="col-12 col-md-auto">
            <form action="" method="get" class="row g-2 align-items-center filter-container">

                <div class="col-6 col-md-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal; ?>">
                    </div>
                </div>

                <div class="col-6 col-md-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-arrow-right"></i></span>
                        <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir; ?>">
                    </div>
                </div>

                <div class="col-12 col-md-auto d-grid d-md-block">
                    <button type="submit" class="btn btn-sm btn-success px-3">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>

            </form>
        </div>

    </div>

    <div class="row g-4 mb-4">

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-bold">Total Pelanggan</p>
                            <h3 class="mb-0 fw-bold text-primary"><?= $total_pelanggan ?></h3>
                        </div>
                        <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded p-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-bold">Cucian Baru</p>
                            <h3 class="mb-0 fw-bold text-danger"><?= $transaksi_baru ?></h3>
                        </div>
                        <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded p-3">
                            <i class="fas fa-tshirt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-bold">Sedang Proses</p>
                            <h3 class="mb-0 fw-bold text-warning"><?= $transaksi_proses ?></h3>
                        </div>
                        <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded p-3">
                            <i class="fas fa-layer-group fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-bold">Siap Diambil</p>
                            <h3 class="mb-0 fw-bold text-success"><?= $transaksi_selesai ?></h3>
                        </div>
                        <div class="icon-shape bg-success bg-opacity-10 text-success rounded p-3">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">&nbsp;</h5>
                    <a href="<?= base_url('transaksi'); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Invoice</th>
                                    <th>Pelanggan</th>
                                    <th>Masuk</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($terbaru)) : ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada transaksi.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($terbaru as $row) : ?>
                                        <tr>
                                            <td class="ps-4 text-primary"><?= $row->kode_invoice ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" style="width:35px; height:35px">
                                                        <i class="fas fa-user text-secondary"></i>
                                                    </div>
                                                    <?= $row->nama ?>
                                                </div>
                                            </td>
                                            <td><?= date('d M Y', strtotime($row->tgl_masuk)); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                if ($row->status == 'Baru') $statusClass = 'bg-secondary';
                                                elseif ($row->status == 'Proses') $statusClass = 'bg-info text-dark';
                                                elseif ($row->status == 'Selesai') $statusClass = 'bg-warning text-dark';
                                                elseif ($row->status == 'Diambil') $statusClass = 'bg-success';
                                                ?>
                                                <span class="badge rounded-pill <?= $statusClass; ?>"><?= $row->status; ?></span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="<?= base_url('transaksi/detail/' . $row->kode_invoice); ?>" class="btn btn-sm btn-light text-primary">
                                                    <i class="fas fa-eye"></i> Detail
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
        </div>
    </div>
</main>
