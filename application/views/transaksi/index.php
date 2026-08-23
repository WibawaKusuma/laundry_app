<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <style>
        .trx-page-hero {
            border: 1px solid rgba(31, 41, 122, 0.1);
            border-radius: 8px;
            background:
                radial-gradient(circle at top left, rgba(31, 41, 122, 0.08), transparent 34%),
                linear-gradient(135deg, #ffffff 0%, #f7f9ff 100%);
            box-shadow: 0 18px 42px rgba(31, 41, 122, 0.08);
        }

        .trx-page-title {
            color: #1f297a;
            letter-spacing: -0.02em;
            margin: 0;
        }

        .trx-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            background: rgba(31, 41, 122, 0.08);
            color: #1f297a;
            padding: 0.45rem 0.8rem;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .trx-filter .input-group-text,
        .trx-filter .form-control {
            border-color: #d7deef;
        }

        .trx-filter .form-control:focus {
            border-color: #1f297a;
            box-shadow: 0 0 0 0.2rem rgba(31, 41, 122, 0.12);
        }

        .trx-period {
            color: #5f688b;
            font-size: 0.9rem;
            white-space: nowrap;
            margin-left: 0.5rem;
        }

        .trx-list-card {
            border: 1px solid rgba(31, 41, 122, 0.08);
            border-radius: 22px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
        }

        .trx-table-overview {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 1rem 1.5rem 0.25rem;
            border-bottom: 1px solid rgba(31, 41, 122, 0.08);
            background: linear-gradient(180deg, rgba(247, 249, 255, 0.9) 0%, rgba(255, 255, 255, 0.95) 100%);
        }

        .trx-overview-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            border: 1px solid rgba(31, 41, 122, 0.08);
            border-radius: 14px;
            background: #fff;
            padding: 0.7rem 0.85rem;
            min-width: 165px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        }

        .trx-overview-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .trx-overview-copy {
            min-width: 0;
        }

        .trx-overview-label {
            display: block;
            color: #667085;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.15rem;
        }

        .trx-overview-value {
            display: flex;
            align-items: baseline;
            gap: 0.45rem;
            color: #17203f;
            line-height: 1;
        }

        .trx-overview-value strong {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .trx-overview-value small {
            color: #667085;
            font-size: 0.78rem;
        }

        .trx-overview-primary .trx-overview-icon,
        .trx-overview-primary .trx-overview-value strong {
            color: #1f297a;
        }

        .trx-overview-primary .trx-overview-icon {
            background: rgba(31, 41, 122, 0.1);
        }

        .trx-overview-danger .trx-overview-icon,
        .trx-overview-danger .trx-overview-value strong {
            color: #dc3545;
        }

        .trx-overview-danger .trx-overview-icon {
            background: rgba(220, 53, 69, 0.1);
        }

        .trx-overview-info .trx-overview-icon,
        .trx-overview-info .trx-overview-value strong {
            color: #0369a1;
        }

        .trx-overview-info .trx-overview-icon {
            background: rgba(3, 105, 161, 0.1);
        }

        .trx-overview-success .trx-overview-icon,
        .trx-overview-success .trx-overview-value strong {
            color: #198754;
        }

        .trx-overview-success .trx-overview-icon {
            background: rgba(25, 135, 84, 0.1);
        }

        .trx-overview-warning .trx-overview-icon,
        .trx-overview-warning .trx-overview-value strong {
            color: #b7791f;
        }

        .trx-overview-warning .trx-overview-icon {
            background: rgba(255, 193, 7, 0.18);
        }

        .trx-list-card .table thead th {
            color: #667085;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .trx-list-card .table> :not(caption)>*>* {
            padding-top: 1rem;
            padding-bottom: 1rem;
            border-color: rgba(31, 41, 122, 0.08);
        }

        .trx-list-card .table tbody tr:hover>* {
            background: rgba(31, 41, 122, 0.03);
        }

        .trx-row-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.45rem;
        }

        .trx-row-meta .badge {
            font-size: 0.72rem;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.45rem 0.65rem;
        }

        .trx-invoice {
            font-weight: 700;
            color: #1f297a;
        }

        .trx-customer {
            font-weight: 600;
            color: #17203f;
        }

        .trx-deadline {
            color: #52607e;
            font-size: 0.82rem;
            line-height: 1.55;
        }

        .trx-deadline .text-danger {
            font-weight: 700;
        }

        .trx-laundry-type {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.5rem;
        }

        .trx-laundry-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 0.28rem 0.55rem;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .trx-laundry-chip-express {
            color: #9a3412;
            background: #ffedd5;
            border-color: #fed7aa;
        }

        .trx-laundry-chip-satu-hari {
            color: #1d4ed8;
            background: #dbeafe;
            border-color: #bfdbfe;
        }

        .trx-laundry-chip-reguler {
            color: #166534;
            background: #dcfce7;
            border-color: #bbf7d0;
        }

        .trx-laundry-chip-neutral {
            color: #52607e;
            background: #f1f5f9;
            border-color: #e2e8f0;
        }

        .trx-page-size {
            width: auto;
            min-width: 82px;
        }

        .trx-pagination .btn {
            min-width: 34px;
        }

        @media (max-width: 991.98px) {
            .trx-period {
                white-space: normal;
                margin-left: 0;
            }

            .trx-table-overview {
                padding: 1rem 1rem 0.25rem;
            }

            .trx-overview-pill {
                min-width: calc(50% - 0.375rem);
            }
        }

        @media (max-width: 575.98px) {
            .trx-overview-pill {
                min-width: 100%;
            }
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="flash-data-success" data-flashdata="<?= $this->session->flashdata('success'); ?>"></div>

    <div class="trx-page-hero p-4 p-lg-4 mb-4">
        <div class="d-flex justify-content-end">
            <form action="" method="get" class="trx-filter row g-2 align-items-center justify-content-end w-100">
                <div class="col-6 col-md-auto col-xl-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-calendar"></i></span>
                        <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal; ?>">
                    </div>
                </div>

                <div class="col-6 col-md-auto col-xl-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-arrow-right"></i></span>
                        <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir; ?>">
                    </div>
                </div>

                <div class="col-12 col-md-auto col-xl-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-money-check-alt"></i></span>
                        <select name="status_bayar" class="form-select">
                            <option value="semua" <?= ($status_bayar ?? 'belum') === 'semua' ? 'selected' : ''; ?>>Semua</option>
                            <option value="sudah" <?= ($status_bayar ?? 'belum') === 'sudah' ? 'selected' : ''; ?>>Sudah Bayar</option>
                            <option value="belum" <?= ($status_bayar ?? 'belum') === 'belum' ? 'selected' : ''; ?>>Belum Bayar</option>
                        </select>
                    </div>
                </div>

                <div class="col-6 col-md-auto col-xl-auto d-grid">
                    <button type="submit" class="btn btn-sm btn-primary" title="Filter Data">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>

                <div class="col-6 col-md-auto col-xl-auto d-grid">
                    <a href="<?= base_url('transaksi/baru'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i> Baru
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="trx-list-card bg-white mb-5">
        <div class="card-header app-section-header py-3 border-0">
            <div class="row align-items-center g-3">

                <div class="col-12 col-lg me-auto">
                    <h5 class="mb-1">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Daftar Nota Operasional
                    </h5>
                    <!-- <small class="text-white-50">Setiap baris menampilkan progres laundry, pembayaran, dan kesiapan pengambilan secara terpisah.</small> -->
                </div>

                <div class="col-12 col-lg-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="searchTransaksi" class="form-control border-start-0" placeholder="Cari nama pelanggan atau invoice...">
                        <button class="btn btn-outline-secondary d-none" type="button" id="btnClearSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-bottom">
            <div class="d-flex align-items-center gap-2 text-muted small">
                <span>Tampilkan</span>
                <select id="transaksiPageSize" class="form-select form-select-sm trx-page-size">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>data</span>
            </div>
            <div id="transaksiPageInfo" class="text-muted small"></div>
        </div>

        <div class="card-body p-0">
            <!-- <div class="trx-table-overview">
                <div class="trx-overview-pill trx-overview-primary">
                    <span class="trx-overview-icon"><i class="fas fa-receipt"></i></span>
                    <div class="trx-overview-copy">
                        <span class="trx-overview-label">Total Nota</span>
                        <span class="trx-overview-value">
                            <strong><?= number_format($ringkasan['total'], 0, ',', '.'); ?></strong>
                            <small>periode aktif</small>
                        </span>
                    </div>
                </div>

                <div class="trx-overview-pill trx-overview-danger">
                    <span class="trx-overview-icon"><i class="fas fa-clock"></i></span>
                    <div class="trx-overview-copy">
                        <span class="trx-overview-label">Belum Lunas</span>
                        <span class="trx-overview-value">
                            <strong><?= number_format($ringkasan['belum_lunas'], 0, ',', '.'); ?></strong>
                            <small>perlu ditagih</small>
                        </span>
                    </div>
                </div>

                <div class="trx-overview-pill trx-overview-info">
                    <span class="trx-overview-icon"><i class="fas fa-box-open"></i></span>
                    <div class="trx-overview-copy">
                        <span class="trx-overview-label">Siap Diambil</span>
                        <span class="trx-overview-value">
                            <strong><?= number_format($ringkasan['siap_diambil'], 0, ',', '.'); ?></strong>
                            <small>tinggal menunggu</small>
                        </span>
                    </div>
                </div>

                <div class="trx-overview-pill trx-overview-success">
                    <span class="trx-overview-icon"><i class="fas fa-hand-holding-heart"></i></span>
                    <div class="trx-overview-copy">
                        <span class="trx-overview-label">Lunas Belum Diambil</span>
                        <span class="trx-overview-value">
                            <strong><?= number_format($ringkasan['lunas_belum_diambil'], 0, ',', '.'); ?></strong>
                            <small>prioritas serah terima</small>
                        </span>
                    </div>
                </div>

                <div class="trx-overview-pill trx-overview-warning">
                    <span class="trx-overview-icon"><i class="fas fa-triangle-exclamation"></i></span>
                    <div class="trx-overview-copy">
                        <span class="trx-overview-label">Lewat Target</span>
                        <span class="trx-overview-value">
                            <strong><?= number_format($ringkasan['terlambat'], 0, ',', '.'); ?></strong>
                            <small>perlu perhatian</small>
                        </span>
                    </div>
                </div>
            </div> -->

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelTransaksi">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Invoice</th>
                            <th>Pelanggan</th>
                            <th>Tanggal Masuk</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Pengambilan / Deadline</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transaksi)) : ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-cash-register fa-3x mb-3"></i>
                                    <p>Belum ada transaksi pada periode ini.</p>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($transaksi as $row) : ?>
                                <?php
                                $st = (string) $row->status;
                                $is_batal = $st === 'Dibatalkan';
                                $is_lunas = (string) $row->dibayar === 'Sudah Dibayar';
                                $is_diambil = $st === 'Diambil';
                                $is_siap_diambil = $st === 'Selesai';
                                $is_lunas_belum_diambil = $is_lunas && $is_siap_diambil;
                                $is_terlambat = !$is_batal && !$is_diambil && !empty($row->batas_waktu) && strtotime($row->batas_waktu) < time();
                                $jenis_cucian = trim((string) ($row->jenis_cucian ?? ''));
                                $jenis_cucian_labels = array_values(array_filter(array_map('trim', explode(',', $jenis_cucian))));

                                $badge = 'bg-secondary';
                                if ($st == 'Proses') {
                                    $badge = 'bg-info text-dark';
                                } elseif ($st == 'Selesai') {
                                    $badge = 'bg-warning text-dark';
                                } elseif ($st == 'Diambil') {
                                    $badge = 'bg-success';
                                } elseif ($is_batal) {
                                    $badge = 'bg-danger';
                                }
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="trx-invoice"><?= $row->kode_invoice; ?></div>
                                        <div class="trx-row-meta">
                                            <?php if ($is_terlambat) : ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Lewat target</span>
                                            <?php endif; ?>
                                            <?php if ($is_lunas_belum_diambil) : ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Prioritas serah terima</span>
                                            <?php elseif ($is_siap_diambil) : ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle">Siap menunggu customer</span>
                                            <?php elseif ($is_batal) : ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nota dibatalkan</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="trx-customer"><?= $row->nama_pelanggan; ?></div>
                                    </td>

                                    <td>
                                        <div class="fw-semibold"><?= date('d/m/Y', strtotime($row->tgl_masuk)); ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($row->tgl_masuk)); ?></small>
                                    </td>

                                    <td>
                                        <span class="badge <?= $badge; ?>"><?= strtoupper($st); ?></span>
                                        <?php if (!empty($jenis_cucian_labels)) : ?>
                                            <div class="trx-laundry-type">
                                                <?php foreach ($jenis_cucian_labels as $jenis_label) : ?>
                                                    <?php
                                                    $jenis_key = strtolower($jenis_label);
                                                    $jenis_class = 'trx-laundry-chip-neutral';
                                                    if ($jenis_key === 'express') {
                                                        $jenis_class = 'trx-laundry-chip-express';
                                                    } elseif ($jenis_key === 'satu hari') {
                                                        $jenis_class = 'trx-laundry-chip-satu-hari';
                                                    } elseif ($jenis_key === 'reguler') {
                                                        $jenis_class = 'trx-laundry-chip-reguler';
                                                    }
                                                    ?>
                                                    <span class="trx-laundry-chip <?= $jenis_class; ?>"><?= htmlspecialchars($jenis_label, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($is_batal) : ?>
                                            <span class="badge bg-light text-danger border border-danger-subtle">Dibatalkan</span>
                                        <?php elseif ($is_lunas) : ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fas fa-check-circle me-1"></i> Lunas</span>
                                            <?php if (!empty($row->tgl_bayar)) : ?>
                                                <div class="mt-2"><small class="text-muted">Dibayar: <?= date('d/m/Y H:i', strtotime($row->tgl_bayar)); ?></small></div>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Belum Bayar</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($is_batal) : ?>
                                            <span class="badge bg-light text-danger border border-danger-subtle">Tidak Diproses</span>
                                            <?php if (!empty($row->batal_at)) : ?>
                                                <div class="mt-2"><small class="text-muted">Batal: <?= date('d/m/Y H:i', strtotime($row->batal_at)); ?></small></div>
                                            <?php endif; ?>
                                        <?php elseif ($is_diambil) : ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Sudah Diambil</span>
                                            <?php if (!empty($row->tgl_diambil)) : ?>
                                                <div class="mt-2"><small class="text-muted">Diambil: <?= date('d/m/Y H:i', strtotime($row->tgl_diambil)); ?></small></div>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="badge bg-light text-dark border"><?= $is_siap_diambil ? 'Belum Diambil' : 'Belum Siap Diambil'; ?></span>
                                            <div class="trx-deadline mt-2">
                                                Deadline:
                                                <span class="<?= $is_terlambat ? 'text-danger' : 'text-muted'; ?>">
                                                    <?= !empty($row->batas_waktu) ? date('d/m/Y H:i', strtotime($row->batas_waktu)) : '-'; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center pe-4">
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
            <div class="d-flex justify-content-end px-4 py-3 border-top">
                <div id="transaksiPagination" class="btn-group btn-group-sm trx-pagination" role="group" aria-label="Paginasi transaksi"></div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('searchTransaksi');
        var btnClear = document.getElementById('btnClearSearch');
        var table = document.getElementById('tabelTransaksi');
        var rows = table ? Array.prototype.slice.call(table.querySelectorAll('tbody tr')) : [];
        var pageSizeSelect = document.getElementById('transaksiPageSize');
        var pageInfo = document.getElementById('transaksiPageInfo');
        var pagination = document.getElementById('transaksiPagination');
        var pageSize = parseInt(pageSizeSelect.value || '25', 10);
        var currentPage = 1;
        var filteredRows = [];

        rows = rows.filter(function(row) {
            return !row.querySelector('td[colspan]');
        });

        function applyFilter(page) {
            var keyword = searchInput.value.toLowerCase().trim();
            btnClear.classList.toggle('d-none', keyword.length === 0);
            filteredRows = rows.filter(function(row) {
                return row.textContent.toLowerCase().indexOf(keyword) > -1;
            });
            renderPage(page || 1);
        }

        function renderPage(page) {
            var totalRows = filteredRows.length;
            var totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
            currentPage = Math.min(Math.max(parseInt(page || 1, 10), 1), totalPages);
            var start = (currentPage - 1) * pageSize;
            var end = start + pageSize;

            rows.forEach(function(row) {
                row.style.display = 'none';
            });

            filteredRows.slice(start, end).forEach(function(row) {
                row.style.display = '';
            });

            if (totalRows === 0) {
                pageInfo.textContent = 'Tidak ada data';
                pagination.innerHTML = '';
                return;
            }

            pageInfo.textContent = 'Menampilkan ' + (start + 1) + '-' + Math.min(end, totalRows) + ' dari ' + totalRows + ' data';
            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            var pages = [];
            pages.push({ label: '&laquo;', page: Math.max(1, currentPage - 1), disabled: currentPage === 1 });

            var startPage = Math.max(1, currentPage - 2);
            var endPage = Math.min(totalPages, currentPage + 2);
            if (currentPage <= 3) {
                endPage = Math.min(totalPages, 5);
            }
            if (currentPage >= totalPages - 2) {
                startPage = Math.max(1, totalPages - 4);
            }

            for (var p = startPage; p <= endPage; p++) {
                pages.push({ label: p, page: p, active: p === currentPage });
            }

            pages.push({ label: '&raquo;', page: Math.min(totalPages, currentPage + 1), disabled: currentPage === totalPages });

            pagination.innerHTML = pages.map(function(item) {
                return '<button type="button" class="btn ' + (item.active ? 'btn-primary' : 'btn-outline-primary') + '" data-page="' + item.page + '"' + (item.disabled ? ' disabled' : '') + '>' + item.label + '</button>';
            }).join('');
        }

        searchInput.addEventListener('keyup', function() {
            applyFilter(1);
        });

        btnClear.addEventListener('click', function() {
            searchInput.value = '';
            btnClear.classList.add('d-none');
            searchInput.focus();
            applyFilter(1);
        });

        pageSizeSelect.addEventListener('change', function() {
            pageSize = parseInt(this.value || '25', 10);
            applyFilter(1);
        });

        pagination.addEventListener('click', function(e) {
            var button = e.target.closest('button[data-page]');
            if (!button || button.disabled) {
                return;
            }

            renderPage(parseInt(button.getAttribute('data-page'), 10));
        });

        applyFilter(1);
    });
</script>
