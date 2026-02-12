<?php
// Script Header untuk memaksa browser download sebagai Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$title.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= $title; ?></title>
    <style>
        /* Sedikit style agar tabel di Excel nanti ada garisnya */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <center>
        <h3>LAPORAN PENGELUARAN LAUNDRY</h3>
        <p>
            Periode: <b><?= date('d F Y', strtotime($tgl_awal)); ?></b>
            s/d
            <b><?= date('d F Y', strtotime($tgl_akhir)); ?></b>
        </p>
    </center>

    <br>

    <table>
        <thead>
            <tr>
                <th style="background-color: #dc3545; color: white;">No</th>
                <th style="background-color: #dc3545; color: white;">Tanggal</th>
                <th style="background-color: #dc3545; color: white;">Keterangan</th>
                <th style="background-color: #dc3545; color: white;">Nominal (Rp)</th>
                <th style="background-color: #dc3545; color: white;">Catatan</th>
                <th style="background-color: #dc3545; color: white;">Diinput Oleh</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            if (empty($pengeluaran)) :
            ?>
                <tr>
                    <td colspan="6" align="center">Tidak ada data pada periode ini</td>
                </tr>
            <?php else : ?>
                <?php foreach ($pengeluaran as $index => $row) :
                    $total += $row->nominal;
                ?>
                    <tr>
                        <td align="center"><?= $index + 1 ?></td>
                        <td align="center"><?= date('d/m/Y', strtotime($row->tgl_pengeluaran)); ?></td>
                        <td><?= $row->keterangan; ?></td>
                        <td align="right"><?= $row->nominal; ?></td>
                        <td><?= $row->catatan; ?></td>
                        <td><?= $row->nama_user; ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="3" align="right" style="font-weight:bold;">TOTAL PENGELUARAN</td>
                    <td align="right" style="font-weight:bold; background-color: #ffcccc;"><?= $total; ?></td>
                    <td colspan="2"></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>

</html>