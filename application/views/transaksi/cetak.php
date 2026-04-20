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
            font-size: 20pt;
            width: 72mm;
            padding: 3mm;
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

        .header .logo {
            width: 110px;
            height: 100px;
            margin: 0 auto 6px;
            display: block;
        }

        .header h2 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .header p {
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.4;
        }

        /* INFO TRANSAKSI */
        .info {
            margin-bottom: 10px;
            font-size: 12pt;
            font-weight: bold;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .customer-name {
            font-size: 16pt;
            font-weight: bold;
            line-height: 1.2;
        }

        /* TABEL ITEM */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            font-size: 13pt;
            margin-bottom: 10px;
        }

        .table-items th {
            border-bottom: 1px dashed #000;
            text-align: left;
            padding: 4px 0;
            font-size: 13pt;
        }

        .table-items td {
            padding: 4px 0;
            vertical-align: top;
            font-weight: bold;
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
            font-size: 13pt;
        }

        .status-bayar {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 10px 0;
            border: 1px solid #000;
            padding: 4px;
        }

        .footer {
            text-align: center;
            font-size: 10pt;
            margin-top: 10px;
            padding-bottom: 20px;
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
            <img src="<?= base_url($company['company_logo']); ?>" alt="Logo" class="logo">
            <h2><?= $company['company_name']; ?></h2>
            <p><?= $company['company_address']; ?></p>
            <p><?= $company['company_phone']; ?></p>
        </div>

        <div class="info">
            <div class="info-row">
                <!-- <span>No: <?= substr($transaksi->kode_invoice, -8); ?></span> <span><?= date('d/m/y H:i'); ?></span> -->
                <span>Tanggal: <?= date('d/m/y H:i'); ?></span>
            </div>
            <div class="info-row">
                <span>Pelanggan: <span class="customer-name"><?= htmlspecialchars(substr($transaksi->nama_pelanggan, 0, 20), ENT_QUOTES, 'UTF-8'); ?></span></span>
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
                    $subtotal = $d->subtotal;
                    $grand_total += $subtotal;
                    $item_label = !empty($d->nama_tipe) ? $d->nama_tipe : $d->nama_paket;
                    $unit_label = !empty($d->nama_satuan) ? strtoupper($d->nama_satuan) : 'KG';
                ?>
                    <tr>
                        <td class="item">
                            <?= htmlspecialchars($item_label, ENT_QUOTES, 'UTF-8'); ?>
                            <?php if (!empty($d->nama_paket) && strcasecmp($d->nama_paket, $item_label) !== 0) : ?>
                                <div style="font-size: 9pt; font-weight: normal;">
                                    Paket: <?= htmlspecialchars($d->nama_paket, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                            <div style="font-size: 9pt; font-weight: normal;">
                                Ket: <?= htmlspecialchars($d->item_note_text ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </td>
                        <td class="qty"><?= $d->qty_label; ?><?= strtolower($unit_label); ?></td>
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

        <?php if (!empty($transaksi->nama_metode_bayar)) : ?>
            <div style="text-align: center; font-size: 9pt; margin-bottom: 5px;">
                Bayar: <?= $transaksi->nama_metode_bayar; ?>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>Terima Kasih</p>
            <p>Simpan struk ini sbg bukti.</p>
            <p>--- <?= $company['company_tagline']; ?> ---</p>
        </div>
    </div>

</body>

</html>
