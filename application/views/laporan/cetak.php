<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($report_meta['title'], ENT_QUOTES, 'UTF-8'); ?></title>
    <?php
    $status_labels = [
        'semua' => 'Semua Status',
        'lunas' => 'Lunas',
        'belum' => 'Belum Lunas',
    ];
    $status_label = isset($status_labels[$status_bayar]) ? $status_labels[$status_bayar] : $status_labels['semua'];
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
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            color: #000;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
            font-size: 18pt;
        }

        .header p {
            margin: 0;
            font-size: 10pt;
            font-style: italic;
        }

        .judul {
            text-align: center;
            margin-bottom: 20px;
        }

        .judul h3 {
            margin: 0;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            font-size: 10pt;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .ttd-area {
            margin-top: 50px;
            float: right;
            width: 200px;
            text-align: center;
        }

        .ttd-line {
            margin-top: 70px;
            border-bottom: 1px solid #000;
        }

        @media print {
            @page {
                size: A4;
                margin: 2cm;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        <h2><?= $company['company_name']; ?></h2>
        <p><?= $company['company_address']; ?> | Telp: <?= $company['company_phone']; ?></p>
    </div>

    <div class="judul">
        <h3><?= strtoupper($report_meta['title']); ?></h3>
        <p>Periode: <?= date('d F Y', strtotime($tgl_awal)) ?> s/d <?= date('d F Y', strtotime($tgl_akhir)) ?></p>
        <p>Jenis Laporan: <?= htmlspecialchars($report_type_labels[$jenis_laporan] ?? ucfirst($jenis_laporan), ENT_QUOTES, 'UTF-8'); ?></p>
        <?php if (!empty($report_meta['status_filter_enabled'])) : ?>
            <p>Filter Status Bayar: <?= $status_label; ?></p>
        <?php endif; ?>
        <p>Nilai laporan dihitung dari subtotal normal dikurangi potongan reward member dan promo gratis.</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%"><?= htmlspecialchars($report_meta['date_label'], ENT_QUOTES, 'UTF-8'); ?></th>
                <th width="16%">Invoice</th>
                <th width="18%">Pelanggan</th>
                <th width="12%"><?= htmlspecialchars($info_column_label, ENT_QUOTES, 'UTF-8'); ?></th>
                <th width="10%">Subtotal</th>
                <th width="10%">Reward</th>
                <th width="10%">Promo</th>
                <th width="11%">Total Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_subtotal = 0;
            $grand_reward = 0;
            $grand_reward_qty = 0;
            $grand_promo = 0;
            $grand_promo_qty = 0;
            $grand_total = 0;
            if (!empty($laporan)) :
                foreach ($laporan as $i => $row) :
                    $grand_subtotal += (float) $row->subtotal_normal;
                    $grand_reward += (float) $row->reward_potongan;
                    $grand_reward_qty += (float) $row->reward_gratis_qty;
                    $grand_promo += (float) $row->promo_gratis_potongan;
                    $grand_promo_qty += (float) $row->promo_gratis_qty;
                    $grand_total += (float) $row->total_akhir;
                    $tanggal_acuan = $row->tgl_masuk;
                    if ($jenis_laporan === 'kas_masuk') {
                        $tanggal_acuan = $row->tgl_bayar;
                    } elseif ($jenis_laporan === 'pengambilan') {
                        $tanggal_acuan = $row->tgl_diambil;
                    }

                    $info_value = $row->dibayar == 'Sudah Dibayar' ? 'Lunas' : 'Belum';
                    if ($jenis_laporan === 'kas_masuk') {
                        $info_value = !empty($row->nama_metode_bayar) ? $row->nama_metode_bayar : 'Tunai';
                    } elseif ($jenis_laporan === 'piutang') {
                        $info_value = 'Belum Lunas';
                    } elseif ($jenis_laporan === 'pengambilan') {
                        $info_value = 'Sudah Diambil';
                    }
            ?>
                    <tr>
                        <td class="text-center"><?= $i + 1; ?></td>
                        <td class="text-center"><?= !empty($tanggal_acuan) ? date('d/m/Y', strtotime($tanggal_acuan)) : '-'; ?></td>
                        <td><?= $row->kode_invoice; ?></td>
                        <td><?= $row->nama_pelanggan; ?></td>
                        <td class="text-center"><?= htmlspecialchars($info_value, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-end">Rp <?= number_format((float) $row->subtotal_normal, 0, ',', '.'); ?></td>
                        <td class="text-end">
                            Rp <?= number_format((float) $row->reward_potongan, 0, ',', '.'); ?>
                            <?php if ((float) $row->reward_gratis_qty > 0) : ?>
                                <div style="font-size:9pt; color:#198754; font-weight:bold;">Gratis <?= rtrim(rtrim(number_format((float) $row->reward_gratis_qty, 2, '.', ''), '0'), '.'); ?> kg</div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            Rp <?= number_format((float) $row->promo_gratis_potongan, 0, ',', '.'); ?>
                            <?php if ((float) $row->promo_gratis_qty > 0) : ?>
                                <div style="font-size:9pt; color:#b45309; font-weight:bold;">Gratis <?= rtrim(rtrim(number_format((float) $row->promo_gratis_qty, 2, '.', ''), '0'), '.'); ?> kg</div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">Rp <?= number_format((float) $row->total_akhir, 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data transaksi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end fw-bold" style="padding: 10px;">TOTAL SUBTOTAL NORMAL :</td>
                <td class="text-end fw-bold">Rp <?= number_format($grand_subtotal, 0, ',', '.'); ?></td>
                <td class="text-end fw-bold">Rp <?= number_format($grand_reward, 0, ',', '.'); ?></td>
                <td class="text-end fw-bold">Rp <?= number_format($grand_promo, 0, ',', '.'); ?></td>
                <td class="text-end fw-bold">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td colspan="8" class="text-end fw-bold" style="padding: 10px;">TOTAL REWARD MEMBER :</td>
                <td class="text-end fw-bold" style="background-color: #f6f6f6;"><?= rtrim(rtrim(number_format($grand_reward_qty, 2, '.', ''), '0'), '.'); ?> kg / Rp <?= number_format($grand_reward, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td colspan="8" class="text-end fw-bold" style="padding: 10px;">TOTAL PROMO GRATIS :</td>
                <td class="text-end fw-bold" style="background-color: #fff4db;"><?= rtrim(rtrim(number_format($grand_promo_qty, 2, '.', ''), '0'), '.'); ?> kg / Rp <?= number_format($grand_promo, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td colspan="8" class="text-end fw-bold" style="padding: 10px;"><?= strtoupper($report_meta['summary_label']); ?> :</td>
                <td class="text-end fw-bold" style="background-color: #ddd;">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="ttd-area">
        <p>Tabanan, <?= date('d F Y'); ?></p>
        <p>Pemilik / Admin</p>
        <div class="ttd-line"></div>
    </div>

</body>

</html>
