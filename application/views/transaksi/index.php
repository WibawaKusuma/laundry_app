<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Riwayat Transaksi</h1>
    </div> -->

    <div class="flash-data-success" data-flashdata="<?= $this->session->flashdata('success'); ?>"></div>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary fw-bold">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Transaksi
                    </h5>
                    <a href="<?= base_url('transaksi/baru'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Invoice</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Status Laundry</th>
                                    <th>Pembayaran</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transaksi)) : ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-cash-register fa-3x mb-3"></i>
                                            <p>Belum ada transaksi hari ini.</p>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php $no = 1;
                                    foreach ($transaksi as $row) : ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class="fw-bold text-primary"><?= $row->kode_invoice; ?></td>
                                            <td><?= date('d/m/Y', strtotime($row->tgl_masuk)); ?></td>
                                            <td><?= $row->nama_pelanggan; ?></td>

                                            <td>
                                                <?php
                                                $st = $row->status;
                                                if ($st == 'Baru') $badge = 'bg-secondary';
                                                elseif ($st == 'Proses') $badge = 'bg-info text-dark';
                                                elseif ($st == 'Selesai') $badge = 'bg-warning text-dark';
                                                else $badge = 'bg-success';
                                                ?>
                                                <span class="badge <?= $badge; ?>"><?= strtoupper($st); ?></span>
                                            </td>

                                            <td>
                                                <?php if ($row->dibayar == 'Sudah Dibayar') : ?>
                                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Lunas</span>
                                                <?php else : ?>
                                                    <span class="badge bg-danger">Belum Bayar</span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-center">
                                                <a href="<?= base_url('transaksi/detail/' . $row->kode_invoice); ?>" class="btn btn-sm btn-outline-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('transaksi/cetak/' . $row->kode_invoice); ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak Invoice">
                                                    <i class="fas fa-print"></i>
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