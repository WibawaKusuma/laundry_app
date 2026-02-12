<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">

        <div class="card-header bg-white py-3">
            <div class="row align-items-center g-3">

                <div class="col-12 col-xl-3">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-chart-line me-2"></i> Laporan Omset
                    </h5>
                </div>

                <div class="col-12 col-xl-9">
                    <form action="<?= base_url('laporan') ?>" method="get">
                        <div class="row g-2 justify-content-xl-end">

                            <div class="col-6 col-md-auto">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar"></i></span>
                                    <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-6 col-md-auto">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-arrow-right"></i></span>
                                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-auto d-grid">
                                <button type="submit" class="btn btn-sm btn-primary px-4" title="Tampilkan Data">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                            </div>

                            <div class="col-auto d-none d-md-block border-start mx-2"></div>

                            <div class="col-6 col-md-auto d-grid">
                                <a href="<?= base_url('laporan/excel?tgl_awal=' . $tgl_awal . '&tgl_akhir=' . $tgl_akhir); ?>" target="_blank" class="btn btn-success btn-sm px-3">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </a>
                            </div>

                            <div class="col-6 col-md-auto d-grid">
                                <a href="<?= base_url('laporan/cetak?tgl_awal=' . $tgl_awal . '&tgl_akhir=' . $tgl_akhir); ?>" target="_blank" class="btn btn-warning btn-sm px-3">
                                    <i class="fas fa-print me-1"></i> PDF
                                </a>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </div>

        <div class="card-body p-0">
            <?php if (empty($laporan)) : ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3 opacity-50"></i>
                    <p class="text-muted">Tidak ada data transaksi pada periode ini.</p>
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Tanggal</th>
                                <th>Invoice</th>
                                <th>Pelanggan</th>
                                <th class="text-center">Status Bayar</th>
                                <th class="text-end pe-4">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $grand_total_omset = 0;
                            foreach ($laporan as $index => $row) :
                                $grand_total_omset += $row->total_harga;
                            ?>
                                <tr>
                                    <td class="ps-4"><?= $index + 1 ?></td>
                                    <td><?= date('d/m/Y', strtotime($row->tgl_masuk)) ?></td>
                                    <td class="fw-bold text-primary"><?= $row->kode_invoice ?></td>
                                    <td><?= $row->nama_pelanggan ?></td>
                                    <td class="text-center">
                                        <?php if ($row->dibayar == 'Sudah Dibayar'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Belum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4 fw-bold">Rp <?= number_format($row->total_harga, 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="5" class="text-end fw-bold py-3">GRAND TOTAL OMSET :</td>
                                <td class="text-end fw-bold text-success py-3 pe-4 fs-6">
                                    Rp <?= number_format($grand_total_omset, 0, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>