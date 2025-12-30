<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?= $transaksi->kode_invoice; ?></title>
    <style>
        /* RESET CSS UNTUK THERMAL PRINTER */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            /* Font kasir klasik */
            font-size: 10pt;
            /* Ukuran font pas */
            width: 58mm;
            /* Lebar kertas thermal standar */
            padding: 2mm;
            color: #000;
            background: #fff;
        }

        /* CONTAINER UTAMA */
        .container {
            width: 100%;
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 8pt;
        }

        /* INFO TRANSAKSI */
        .info {
            margin-bottom: 10px;
            font-size: 8pt;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
        }

        /* TABEL ITEM */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 10px;
        }

        .table-items th {
            border-bottom: 1px dashed #000;
            text-align: left;
            padding: 2px 0;
        }

        .table-items td {
            padding: 2px 0;
            vertical-align: top;
        }

        .qty {
            width: 15%;
            text-align: center;
        }

        .item {
            width: 55%;
        }

        .price {
            width: 30%;
            text-align: right;
        }

        /* TOTAL DAN FOOTER */
        .summary {
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-bottom: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 10pt;
        }

        .status-bayar {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
            border: 1px solid #000;
            padding: 2px;
        }

        .footer {
            text-align: center;
            font-size: 8pt;
            margin-top: 10px;
            padding-bottom: 20px;
            /* Jarak bawah agar kertas bisa disobek */
        }

        /* PENTING: MENGHILANGKAN HEADER/FOOTER BROWSER (URL, DATE, DLL) */
        @media print {
            @page {
                margin: 0;
                size: auto;
            }

            body {
                margin: 0;
                padding: 2mm;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="container">
        <div class="header">
            <h2>LAUNDRY APP</h2>
            <p>Jl. Mawar Melati No. 123<br>Tabanan, Bali</p>
            <p>0812-3456-7890</p>
        </div>

        <div class="info">
            <div class="info-row">
                <span>No: <?= substr($transaksi->kode_invoice, -8); ?></span> <span><?= date('d/m/y H:i'); ?></span>
            </div>
            <div class="info-row">
                <span>Plg: <?= substr($transaksi->nama_pelanggan, 0, 15); ?></span>
            </div>
        </div>

        <table class="table-items">
            <thead>
                <tr>
                    <th class="item">Item</th>
                    <th class="qty">Qty</th>
                    <th class="price">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                foreach ($detail as $d) :
                    $subtotal = $d->harga * $d->qty;
                    $grand_total += $subtotal;
                ?>
                    <tr>
                        <td class="item"><?= $d->nama_paket; ?></td>
                        <td class="qty"><?= $d->qty; ?></td>
                        <td class="price"><?= number_format($subtotal, 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-row">
                <span>TOTAL:</span>
                <span>Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
            </div>
        </div>

        <div class="status-bayar">
            <?= strtoupper($transaksi->dibayar); ?>
        </div>

        <div class="footer">
            <p>Terima Kasih</p>
            <p>Simpan struk ini sbg bukti.</p>
            <p>--- Layanan Laundry Kilat ---</p>
        </div>
    </div>

</body>

</html>