<?php
$status_labels = [
    'semua' => 'Semua status bayar',
    'lunas' => 'Hanya yang sudah lunas',
    'belum' => 'Hanya yang belum lunas',
];
$active_status_label = isset($status_labels[$status_bayar]) ? $status_labels[$status_bayar] : $status_labels['semua'];
$filter_query = http_build_query([
    'tgl_awal' => $tgl_awal,
    'tgl_akhir' => $tgl_akhir,
    'jenis_laporan' => $jenis_laporan,
    'status_bayar' => $status_bayar,
]);

$report_type_labels = [
    'omset' => 'Omset',
    'kas_masuk' => 'Kas Masuk',
    'piutang' => 'Piutang',
    'pengambilan' => 'Pengambilan',
];

$info_column_label = 'Status / Info';
if ($jenis_laporan === 'kas_masuk') {
    $info_column_label = 'Metode Bayar';
} elseif ($jenis_laporan === 'pengambilan') {
    $info_column_label = 'Status Pengambilan';
}
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <style>
        .report-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .report-toolbar-title {
            flex: 0 0 auto;
        }

        .report-toolbar-form {
            flex: 1 1 auto;
        }

        .report-filter-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
            gap: 0.5rem;
        }

        .report-filter-item {
            flex: 0 0 auto;
        }

        .report-filter-item .input-group-sm,
        .report-filter-item .btn-sm {
            height: 100%;
        }

        .report-type-field {
            width: 230px;
        }

        .report-date-field {
            width: 235px;
        }

        .report-status-field {
            width: 205px;
        }

        .report-action-btn {
            min-width: 116px;
        }

        .report-icon-btn {
            min-width: 68px;
        }

        .report-toolbar-divider {
            width: 1px;
            height: 2.4rem;
            background: rgba(31, 41, 122, 0.12);
            margin-inline: 0.25rem;
        }

        @media (max-width: 1199.98px) {
            .report-toolbar {
                align-items: stretch;
            }

            .report-toolbar-form {
                width: 100%;
            }

            .report-filter-row {
                justify-content: stretch;
            }

            .report-filter-item {
                width: 100%;
            }

            .report-type-field,
            .report-date-field,
            .report-status-field,
            .report-action-btn,
            .report-icon-btn {
                width: 100%;
            }

            .report-toolbar-divider {
                display: none;
            }
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">

        <div class="card-header app-section-header py-3">
            <div class="report-toolbar">
                <div class="report-toolbar-title">
                    <h5 class="mb-0">
                        <i class="fas <?= htmlspecialchars($report_meta['icon'], ENT_QUOTES, 'UTF-8'); ?> me-2"></i> <?= htmlspecialchars($report_meta['heading'], ENT_QUOTES, 'UTF-8'); ?>
                    </h5>
                </div>

                <div class="report-toolbar-form">
                    <form action="<?= base_url('laporan') ?>" method="get">
                        <div class="report-filter-row">

                            <div class="report-filter-item">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-layer-group"></i></span>
                                    <select name="jenis_laporan" class="form-select report-type-field">
                                        <?php foreach ($report_type_labels as $value => $label) : ?>
                                            <option value="<?= $value; ?>" <?= $jenis_laporan === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="report-filter-item">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar"></i></span>
                                    <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control report-date-field" required>
                                </div>
                            </div>

                            <div class="report-filter-item">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-arrow-right"></i></span>
                                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control report-date-field" required>
                                </div>
                            </div>

                            <?php if (!empty($report_meta['status_filter_enabled'])) : ?>
                                <div class="report-filter-item">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-wallet"></i></span>
                                        <select name="status_bayar" class="form-select report-status-field">
                                            <option value="semua" <?= $status_bayar == 'semua' ? 'selected' : '' ?>>Semua Status</option>
                                            <option value="lunas" <?= $status_bayar == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                            <option value="belum" <?= $status_bayar == 'belum' ? 'selected' : '' ?>>Belum Lunas</option>
                                        </select>
                                    </div>
                                </div>
                            <?php else : ?>
                                <input type="hidden" name="status_bayar" value="<?= htmlspecialchars($status_bayar, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>

                            <div class="report-filter-item">
                                <button type="submit" class="btn btn-sm btn-primary px-4 report-action-btn" title="Tampilkan Data">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                            </div>

                            <!-- <div class="report-toolbar-divider d-none d-xl-block"></div> -->

                            <div class="report-filter-item">
                                <a href="<?= base_url('laporan/excel?' . $filter_query); ?>" target="_blank" class="btn btn-success btn-sm px-3 report-icon-btn">
                                    <i class="fas fa-file-excel me-1"></i>
                                </a>
                            </div>

                            <div class="report-filter-item">
                                <a href="<?= base_url('laporan/cetak?' . $filter_query); ?>" target="_blank" class="btn btn-warning btn-sm px-3 report-icon-btn">
                                    <i class="fas fa-print me-1"></i>
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
                    <p class="text-muted mb-1"><?= htmlspecialchars($report_meta['empty_message'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <small class="text-muted">
                        Jenis laporan: <?= htmlspecialchars($report_type_labels[$jenis_laporan] ?? ucfirst($jenis_laporan), ENT_QUOTES, 'UTF-8'); ?>
                        <?php if (!empty($report_meta['status_filter_enabled'])) : ?>
                            | Filter bayar: <?= htmlspecialchars($active_status_label, ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </small>
                </div>
            <?php else : ?>
                <div class="px-4 pt-3 pb-2 border-bottom bg-light-subtle">
                    <small class="text-muted">
                        Jenis laporan aktif:
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($report_type_labels[$jenis_laporan] ?? ucfirst($jenis_laporan), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($report_meta['status_filter_enabled'])) : ?>
                            | Filter bayar:
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($active_status_label, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">No</th>
                                <th><?= htmlspecialchars($report_meta['date_label'], ENT_QUOTES, 'UTF-8'); ?></th>
                                <th>Invoice</th>
                                <th>Pelanggan</th>
                                <th class="text-center"><?= htmlspecialchars($info_column_label, ENT_QUOTES, 'UTF-8'); ?></th>
                                <th class="text-end pe-4">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $grand_total = 0;
                            foreach ($laporan as $index => $row) :
                                $grand_total += (float) $row->total_harga;

                                $tanggal_acuan = $row->tgl_masuk;
                                if ($jenis_laporan === 'kas_masuk') {
                                    $tanggal_acuan = $row->tgl_bayar;
                                } elseif ($jenis_laporan === 'pengambilan') {
                                    $tanggal_acuan = $row->tgl_diambil;
                                }
                            ?>
                                <tr>
                                    <td class="ps-4"><?= $index + 1 ?></td>
                                    <td><?= !empty($tanggal_acuan) ? date('d/m/Y', strtotime($tanggal_acuan)) : '-' ?></td>
                                    <td class="fw-bold text-primary"><?= $row->kode_invoice ?></td>
                                    <td><?= $row->nama_pelanggan ?></td>
                                    <td class="text-center">
                                        <?php if ($jenis_laporan === 'kas_masuk') : ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                <?= !empty($row->nama_metode_bayar) ? htmlspecialchars($row->nama_metode_bayar, ENT_QUOTES, 'UTF-8') : 'Tunai'; ?>
                                            </span>
                                        <?php elseif ($jenis_laporan === 'piutang') : ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Belum Lunas</span>
                                        <?php elseif ($jenis_laporan === 'pengambilan') : ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Sudah Diambil</span>
                                        <?php else : ?>
                                            <?php if ($row->dibayar == 'Sudah Dibayar') : ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Lunas</span>
                                            <?php else : ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Belum</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4 fw-bold">Rp <?= number_format($row->total_harga, 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="5" class="text-end fw-bold py-3"><?= strtoupper($report_meta['summary_label']); ?> :</td>
                                <td class="text-end fw-bold text-success py-3 pe-4 fs-6">
                                    Rp <?= number_format($grand_total, 0, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>