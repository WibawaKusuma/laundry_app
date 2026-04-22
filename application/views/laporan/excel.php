<?php
// Skrip untuk memaksa download file Excel
$status_labels = [
    'semua' => 'Semua Status',
    'lunas' => 'Lunas',
    'belum' => 'Belum Lunas',
];
$status_label = isset($status_labels[$status_bayar]) ? $status_labels[$status_bayar] : $status_labels['semua'];
$filename = "Laporan_Laundry_" . $tgl_awal . "_sd_" . $tgl_akhir;
if ($status_bayar !== 'semua') {
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
        /* CSS Inline agar terbaca di Excel */
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

    <h3 style="text-align: center;">LAPORAN PENDAPATAN LAUNDRY</h3>
    <p style="text-align: center;">Periode: <?= date('d F Y', strtotime($tgl_awal)) ?> - <?= date('d F Y', strtotime($tgl_akhir)) ?></p>
    <p style="text-align: center;">Filter Status Bayar: <?= $status_label; ?></p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Masuk</th>
                <th>No Invoice</th>
                <th>Nama Pelanggan</th>
                <th>Status Bayar</th>
                <th>Total Pendapatan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total = 0;
            if (!empty($laporan)) :
                foreach ($laporan as $i => $row) :
                    $grand_total += $row->total_harga;
            ?>
                    <tr>
                        <td class="text-center"><?= $i + 1; ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row->tgl_masuk)); ?></td>
                        <td style="mso-number-format:'\@';"><?= $row->kode_invoice; ?></td>
                        <td><?= $row->nama_pelanggan; ?></td>
                        <td class="text-center"><?= $row->dibayar == 'Sudah Dibayar' ? 'Lunas' : 'Belum Lunas'; ?></td>
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
                <td colspan="5" class="text-end" style="font-weight:bold;">TOTAL OMSET :</td>
                <td class="text-end" style="font-weight:bold; background-color: #ffff00;"><?= $grand_total; ?></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
