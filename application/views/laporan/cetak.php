<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <?php
    $status_labels = [
        'semua' => 'Semua Status',
        'lunas' => 'Lunas',
        'belum' => 'Belum Lunas',
    ];
    $status_label = isset($status_labels[$status_bayar]) ? $status_labels[$status_bayar] : $status_labels['semua'];
    ?>
    <style>
        /* CSS KHUSUS CETAK A4 */
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            padding: 20px;
        }

        /* Kop Surat Sederhana */
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

        /* Judul Laporan */
        .judul {
            text-align: center;
            margin-bottom: 20px;
        }

        .judul h3 {
            margin: 0;
            margin-bottom: 5px;
        }

        /* Tabel Data */
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
            font-size: 11pt;
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

        /* Area Tanda Tangan */
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

        /* Hapus elemen browser saat print */
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
        <h3>LAPORAN PENDAPATAN</h3>
        <p>Periode: <?= date('d F Y', strtotime($tgl_awal)) ?> s/d <?= date('d F Y', strtotime($tgl_akhir)) ?></p>
        <p>Filter Status Bayar: <?= $status_label; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Invoice</th>
                <th width="25%">Pelanggan</th>
                <th width="15%">Status</th>
                <th width="20%">Pendapatan</th>
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
                        <td><?= $row->kode_invoice; ?></td>
                        <td><?= $row->nama_pelanggan; ?></td>
                        <td class="text-center"><?= $row->dibayar == 'Sudah Dibayar' ? 'Lunas' : 'Belum'; ?></td>
                        <td class="text-end">Rp <?= number_format($row->total_harga, 0, ',', '.'); ?></td>
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
                <td colspan="5" class="text-end fw-bold" style="padding: 10px;">TOTAL OMSET :</td>
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
