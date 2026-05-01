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

$filename = "Laporan_" . str_replace(' ', '_', $report_meta['heading']) . "_" . $tgl_awal . "_sd_" . $tgl_akhir;
if (!empty($report_meta['status_filter_enabled']) && $status_bayar !== 'semua') {
    $filename .= "_" . str_replace(' ', '_', $status_label);
}
$filename .= ".xls";

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Export Excel</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }
    </style>
</head>

<body>

    <h3 style="text-align: center;"><?= strtoupper($report_meta['title']); ?></h3>
    <p style="text-align: center;">Periode: <?= date('d F Y', strtotime($tgl_awal)) ?> - <?= date('d F Y', strtotime($tgl_akhir)) ?></p>
    <p style="text-align: center;">Jenis Laporan: <?= htmlspecialchars($report_type_labels[$jenis_laporan] ?? ucfirst($jenis_laporan), ENT_QUOTES, 'UTF-8'); ?></p>
    <?php if (!empty($report_meta['status_filter_enabled'])) : ?>
        <p style="text-align: center;">Filter Status Bayar: <?= $status_label; ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th><?= htmlspecialchars($report_meta['date_label'], ENT_QUOTES, 'UTF-8'); ?></th>
                <th>No Invoice</th>
                <th>Nama Pelanggan</th>
                <th><?= htmlspecialchars($info_column_label, ENT_QUOTES, 'UTF-8'); ?></th>
                <th>Total Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total = 0;
            if (!empty($laporan)) :
                foreach ($laporan as $i => $row) :
                    $grand_total += (float) $row->total_harga;
                    $tanggal_acuan = $row->tgl_masuk;
                    if ($jenis_laporan === 'kas_masuk') {
                        $tanggal_acuan = $row->tgl_bayar;
                    } elseif ($jenis_laporan === 'pengambilan') {
                        $tanggal_acuan = $row->tgl_diambil;
                    }

                    $info_value = $row->dibayar == 'Sudah Dibayar' ? 'Lunas' : 'Belum Lunas';
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
                        <td style="mso-number-format:'\@';"><?= $row->kode_invoice; ?></td>
                        <td><?= $row->nama_pelanggan; ?></td>
                        <td class="text-center"><?= htmlspecialchars($info_value, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-end"><?= $row->total_harga; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data transaksi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end" style="font-weight:bold;"><?= strtoupper($report_meta['summary_label']); ?> :</td>
                <td class="text-end" style="font-weight:bold; background-color: #ffff00;"><?= $grand_total; ?></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
