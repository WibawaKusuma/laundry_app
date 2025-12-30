<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Laporan Transaksi & Omset</h1>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="<?= base_url('laporan') ?>" method="get">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="tgl_awal" class="form-label fw-bold">Tanggal Awal</label>
                        <input type="date" id="tgl_awal" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control">
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <label for="tgl_akhir" class="form-label fw-bold">Tanggal Akhir</label>
                        <input type="date" id="tgl_akhir" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control">
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($laporan)) : ?>
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="float-end">
                    <a href="<?= base_url('laporan/excel?tgl_awal=' . $tgl_awal . '&tgl_akhir=' . $tgl_akhir); ?>" target="_blank" class="btn btn-outline-success btn-sm me-2">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>

                    <a href="<?= base_url('laporan/cetak?tgl_awal=' . $tgl_awal . '&tgl_akhir=' . $tgl_akhir); ?>" target="_blank" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-print me-1"></i> Cetak PDF
                    </a>
                </div>
                <h5 class="mb-0 fw-bold">Hasil Laporan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Invoice</th>
                                <th>Pelanggan</th>
                                <th>Status Bayar</th>
                                <th class="text-end">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $grand_total_omset = 0;
                            foreach ($laporan as $index => $row) :
                                $grand_total_omset += $row->total_harga;
                            ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= date('d/m/Y', strtotime($row->tgl_masuk)) ?></td>
                                    <td class="fw-bold"><?= $row->kode_invoice ?></td>
                                    <td><?= $row->nama_pelanggan ?></td>
                                    <td class="text-center">
                                        <?php if ($row->dibayar == 'Sudah Dibayar'): ?>
                                            <span class="badge bg-success">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Belum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">Rp <?= number_format($row->total_harga, 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-white">
                            <tr style="border-top: 2px solid #333;">
                                <td colspan="5" class="text-end fw-bold">Grand Total :</td>
                                <td class="text-end fw-bold text-dark">
                                    Rp <?= number_format($grand_total_omset, 0, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center mt-4">
            <i class="fas fa-info-circle me-2"></i> Tidak ada data transaksi pada periode tanggal tersebut.
        </div>
    <?php endif; ?>

</main>