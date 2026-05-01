<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <style>
        .dashboard-hero {
            border: 1px solid rgba(31, 41, 122, 0.1);
            border-radius: 8px;
            background:
                radial-gradient(circle at top left, rgba(31, 41, 122, 0.09), transparent 34%),
                linear-gradient(135deg, #ffffff 0%, #f7f9ff 100%);
            box-shadow: 0 18px 42px rgba(31, 41, 122, 0.08);
        }

        .dashboard-hero-title {
            color: #1f297a;
            letter-spacing: -0.02em;
        }

        .dashboard-hero-note {
            color: #5f688b;
            line-height: 1.65;
            max-width: 720px;
        }

        .dashboard-kicker {
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

        .dashboard-filter .input-group-text,
        .dashboard-filter .form-control {
            border-color: #d7deef;
        }

        .dashboard-filter .form-control:focus {
            border-color: #1f297a;
            box-shadow: 0 0 0 0.2rem rgba(31, 41, 122, 0.12);
        }

        .dashboard-section-title {
            color: #1f297a;
            letter-spacing: -0.01em;
        }

        .metric-card {
            border: 1px solid rgba(31, 41, 122, 0.08);
            border-radius: 20px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
        }

        .metric-card .metric-label {
            color: #68738f;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.45rem;
        }

        .metric-card .metric-value {
            color: #17203f;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.05;
            margin-bottom: 0.3rem;
        }

        .metric-card .metric-note {
            color: #667085;
            font-size: 0.84rem;
            line-height: 1.55;
            margin-bottom: 0;
        }

        .metric-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .metric-card.metric-finance-primary .metric-value,
        .metric-card.metric-finance-primary .metric-icon {
            color: #1f297a;
        }

        .metric-card.metric-finance-primary .metric-icon {
            background: rgba(31, 41, 122, 0.1);
        }

        .metric-card.metric-finance-success .metric-value,
        .metric-card.metric-finance-success .metric-icon {
            color: #198754;
        }

        .metric-card.metric-finance-success .metric-icon {
            background: rgba(25, 135, 84, 0.1);
        }

        .metric-card.metric-finance-danger .metric-value,
        .metric-card.metric-finance-danger .metric-icon {
            color: #dc3545;
        }

        .metric-card.metric-finance-danger .metric-icon {
            background: rgba(220, 53, 69, 0.1);
        }

        .metric-card.metric-finance-warning .metric-value,
        .metric-card.metric-finance-warning .metric-icon {
            color: #b7791f;
        }

        .metric-card.metric-finance-warning .metric-icon {
            background: rgba(255, 193, 7, 0.18);
        }

        .metric-card.metric-finance-dark .metric-value,
        .metric-card.metric-finance-dark .metric-icon {
            color: #0f172a;
        }

        .metric-card.metric-finance-dark .metric-icon {
            background: rgba(15, 23, 42, 0.08);
        }

        .metric-card.metric-ops-secondary .metric-value,
        .metric-card.metric-ops-secondary .metric-icon {
            color: #475467;
        }

        .metric-card.metric-ops-secondary .metric-icon {
            background: rgba(71, 84, 103, 0.1);
        }

        .metric-card.metric-ops-info .metric-value,
        .metric-card.metric-ops-info .metric-icon {
            color: #0f766e;
        }

        .metric-card.metric-ops-info .metric-icon {
            background: rgba(15, 118, 110, 0.1);
        }

        .metric-card.metric-ops-ready .metric-value,
        .metric-card.metric-ops-ready .metric-icon {
            color: #0369a1;
        }

        .metric-card.metric-ops-ready .metric-icon {
            background: rgba(3, 105, 161, 0.1);
        }

        .recent-card {
            border: 1px solid rgba(31, 41, 122, 0.08);
            border-radius: 8px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .recent-card-header {
            padding: 1.35rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(31, 41, 122, 0.08);
            background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%);
        }

        .recent-card-title {
            color: #17203f;
            letter-spacing: -0.02em;
        }

        .recent-card-note {
            color: #667085;
            line-height: 1.5;
        }

        .recent-card-link.btn {
            border-color: rgba(31, 41, 122, 0.75);
            color: #1f297a;
            padding-inline: 0.95rem;
            border-radius: 12px;
        }

        .recent-card-link.btn:hover,
        .recent-card-link.btn:focus {
            background: #1f297a;
            border-color: #1f297a;
            color: #fff;
        }

        .recent-card .table thead th {
            color: #667085;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            background: #f8faff;
            border-bottom-color: rgba(31, 41, 122, 0.08);
        }

        .recent-card .table> :not(caption)>*>* {
            padding-top: 1rem;
            padding-bottom: 1rem;
            border-color: rgba(31, 41, 122, 0.08);
            vertical-align: middle;
        }

        .recent-card .table tbody tr:hover>* {
            background: rgba(31, 41, 122, 0.03);
        }

        .recent-invoice {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: #1f297a;
            font-weight: 700;
        }

        .recent-invoice::before {
            content: "";
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background: rgba(31, 41, 122, 0.2);
            box-shadow: 0 0 0 6px rgba(31, 41, 122, 0.08);
            flex-shrink: 0;
        }

        .recent-customer {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .recent-avatar {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(31, 41, 122, 0.1) 0%, rgba(31, 41, 122, 0.18) 100%);
            color: #1f297a;
            flex-shrink: 0;
        }

        .recent-customer-name {
            color: #17203f;
            font-weight: 600;
            line-height: 1.35;
        }

        .recent-date {
            color: #17203f;
            font-weight: 600;
            line-height: 1.25;
        }

        .recent-date small {
            display: block;
            color: #667085;
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 0.2rem;
        }

        .recent-detail-btn.btn {
            border-radius: 12px;
            border-color: rgba(31, 41, 122, 0.12);
            background: #f8faff;
            color: #1f297a;
            font-weight: 600;
            padding-inline: 0.85rem;
        }

        .recent-detail-btn.btn:hover,
        .recent-detail-btn.btn:focus {
            background: #1f297a;
            border-color: #1f297a;
            color: #fff;
        }

        .recent-empty {
            padding: 2.5rem 1.5rem;
            text-align: center;
            color: #667085;
        }

        .recent-empty-icon {
            width: 3.5rem;
            height: 3.5rem;
            margin: 0 auto 0.9rem;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(31, 41, 122, 0.08);
            color: #1f297a;
            font-size: 1.2rem;
        }

        .recent-empty p {
            margin-bottom: 0;
            font-weight: 500;
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="dashboard-hero p-4 p-lg-4 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-12 col-xl-7">
                <span class="dashboard-kicker"><i class="fas fa-water"></i> Ringkasan Laundry</span>
                <h4 class="dashboard-hero-title fw-bold mt-3 mb-2">Dashboard operasional dan bisnis dalam satu periode.</h4>
                <p class="dashboard-hero-note mb-0">
                    Omset dihitung dari order masuk, kas masuk dihitung dari pembayaran yang benar-benar diterima, dan piutang menunjukkan nilai order yang masih belum lunas.
                </p>
            </div>
            <div class="col-12 col-xl-5">
                <form action="" method="get" class="dashboard-filter row g-2 align-items-center justify-content-xl-end">
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

                    <div class="col-12 col-md-auto d-grid">
                        <button type="submit" class="btn btn-sm btn-primary px-3">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                </form>
                <div class="text-xl-end mt-3">
                    <small class="text-muted">Periode aktif: <?= date('d M Y', strtotime($tgl_awal)); ?> s/d <?= date('d M Y', strtotime($tgl_akhir)); ?></small>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($is_admin)) : ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="dashboard-section-title fw-bold mb-1">Ringkasan Bisnis</h5>
                <small class="text-muted">Blok ini membantu owner membaca posisi order, kas, dan piutang tanpa angka campuran.</small>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-xl-4">
                <div class="metric-card metric-finance-primary h-100 bg-white">
                    <div class="card-body p-4 d-flex justify-content-between gap-3">
                        <div>
                            <p class="metric-label">Omset Periode</p>
                            <h3 class="metric-value">Rp <?= number_format($omset_periode, 0, ',', '.'); ?></h3>
                            <p class="metric-note">Nilai semua order yang masuk berdasarkan tanggal order diterima.</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-chart-line"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="metric-card metric-finance-success h-100 bg-white">
                    <div class="card-body p-4 d-flex justify-content-between gap-3">
                        <div>
                            <p class="metric-label">Kas Masuk</p>
                            <h3 class="metric-value">Rp <?= number_format($kas_masuk_periode, 0, ',', '.'); ?></h3>
                            <p class="metric-note">Uang yang benar-benar diterima pada periode ini berdasarkan tanggal bayar.</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-wallet"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="metric-card metric-finance-danger h-100 bg-white">
                    <div class="card-body p-4 d-flex justify-content-between gap-3">
                        <div>
                            <p class="metric-label">Piutang Periode</p>
                            <h3 class="metric-value">Rp <?= number_format($piutang_periode, 0, ',', '.'); ?></h3>
                            <p class="metric-note">Nilai order pada periode aktif yang pembayaran customer-nya masih belum lunas.</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="metric-card metric-finance-warning h-100 bg-white">
                    <div class="card-body p-4 d-flex justify-content-between gap-3">
                        <div>
                            <p class="metric-label">Pengeluaran</p>
                            <h3 class="metric-value">Rp <?= number_format($pengeluaran_periode, 0, ',', '.'); ?></h3>
                            <p class="metric-note">Total uang keluar yang sudah dicatat pada menu pengeluaran selama periode aktif.</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-money-bill-wave"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="metric-card metric-finance-dark h-100 bg-white">
                    <div class="card-body p-4 d-flex justify-content-between gap-3">
                        <div>
                            <p class="metric-label">Saldo Operasional</p>
                            <h3 class="metric-value">Rp <?= number_format($saldo_operasional, 0, ',', '.'); ?></h3>
                            <p class="metric-note">Kas masuk dikurangi pengeluaran. Ini membantu membaca posisi operasional periode aktif.</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-scale-balanced"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="metric-card metric-finance-primary h-100 bg-white">
                    <div class="card-body p-4 d-flex justify-content-between gap-3">
                        <div>
                            <p class="metric-label">Total Pelanggan</p>
                            <h3 class="metric-value"><?= number_format($total_pelanggan, 0, ',', '.'); ?></h3>
                            <p class="metric-note">Jumlah pelanggan terdaftar. Berguna untuk membaca skala basis customer saat ini.</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-users"></i></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="dashboard-section-title fw-bold mb-1">Ringkasan Operasional</h5>
            <small class="text-muted">Blok ini fokus pada antrean kerja dan readiness pengambilan.</small>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="metric-card metric-ops-secondary h-100 bg-white">
                <div class="card-body p-4 d-flex justify-content-between gap-3">
                    <div>
                        <p class="metric-label">Order Baru</p>
                        <h3 class="metric-value"><?= $transaksi_baru ?></h3>
                        <p class="metric-note">Transaksi baru yang masuk dan belum mulai diproses pada periode ini.</p>
                    </div>
                    <span class="metric-icon"><i class="fas fa-inbox"></i></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="metric-card metric-ops-info h-100 bg-white">
                <div class="card-body p-4 d-flex justify-content-between gap-3">
                    <div>
                        <p class="metric-label">Sedang Proses</p>
                        <h3 class="metric-value"><?= $transaksi_proses ?></h3>
                        <p class="metric-note">Laundry yang sudah masuk pengerjaan dan masih aktif di area produksi.</p>
                    </div>
                    <span class="metric-icon"><i class="fas fa-soap"></i></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="metric-card metric-ops-ready h-100 bg-white">
                <div class="card-body p-4 d-flex justify-content-between gap-3">
                    <div>
                        <p class="metric-label">Siap Diambil</p>
                        <h3 class="metric-value"><?= $transaksi_selesai ?></h3>
                        <p class="metric-note">Laundry yang sudah selesai dikerjakan dan tinggal menunggu pengambilan customer.</p>
                    </div>
                    <span class="metric-icon"><i class="fas fa-check-circle"></i></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="metric-card metric-finance-danger h-100 bg-white">
                <div class="card-body p-4 d-flex justify-content-between gap-3">
                    <div>
                        <p class="metric-label">Belum Lunas</p>
                        <h3 class="metric-value"><?= $transaksi_belum_lunas ?></h3>
                        <p class="metric-note">Jumlah order pada periode ini yang masih menunggu pelunasan.</p>
                    </div>
                    <span class="metric-icon"><i class="fas fa-clock"></i></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="metric-card metric-finance-success h-100 bg-white">
                <div class="card-body p-4 d-flex justify-content-between gap-3">
                    <div>
                        <p class="metric-label">Lunas Belum Diambil</p>
                        <h3 class="metric-value"><?= $transaksi_lunas_belum_diambil ?></h3>
                        <p class="metric-note">Laundry yang sudah lunas dan siap diambil, tetapi belum diserahkan ke customer.</p>
                    </div>
                    <span class="metric-icon"><i class="fas fa-hand-holding-heart"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="recent-card bg-white mb-5">
        <div class="recent-card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <h5 class="recent-card-title mb-1 fw-bold">Transaksi Terbaru Pada Periode Aktif</h5>
                <small class="recent-card-note">Daftar ini tetap memakai tanggal masuk transaksi agar selaras dengan filter dashboard.</small>
            </div>
            <a href="<?= base_url('transaksi'); ?>" class="recent-card-link btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Invoice</th>
                            <th>Pelanggan</th>
                            <th>Masuk</th>
                            <th>Status Laundry</th>
                            <th>Status Pembayaran</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($terbaru)) : ?>
                            <tr>
                                <td colspan="6" class="recent-empty">
                                    <div class="recent-empty-icon"><i class="fas fa-receipt"></i></div>
                                    <p>Belum ada transaksi pada periode ini.</p>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($terbaru as $row) : ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="recent-invoice"><?= $row->kode_invoice ?></span>
                                    </td>
                                    <td>
                                        <div class="recent-customer">
                                            <div class="recent-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="recent-customer-name"><?= $row->nama ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="recent-date">
                                            <?= date('d M Y', strtotime($row->tgl_masuk)); ?>
                                            <small><?= date('H:i', strtotime($row->tgl_masuk)); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        if ($row->status == 'Proses') {
                                            $statusClass = 'bg-info text-dark';
                                        } elseif ($row->status == 'Selesai') {
                                            $statusClass = 'bg-warning text-dark';
                                        } elseif ($row->status == 'Diambil') {
                                            $statusClass = 'bg-success';
                                        }
                                        ?>
                                        <span class="badge rounded-pill <?= $statusClass; ?>"><?= $row->status; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row->dibayar === 'Sudah Dibayar') : ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">Lunas</span>
                                        <?php else : ?>
                                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">Belum Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="<?= base_url('transaksi/detail/' . $row->kode_invoice); ?>" class="recent-detail-btn btn btn-sm btn-light text-primary">
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
</main>