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
            color: #000;
            background: #f1f5f9;
            margin: 0;
            padding: 0;
        }

        /* ACTION BAR DI LAYAR (NO-PRINT) */
        .action-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            background: #1e293b;
            padding: 12px 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            min-height: 44px;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-back {
            background: #334155;
            color: #ffffff;
        }

        .btn-back:hover,
        .btn-back:active {
            background: #475569;
        }

        .btn-print {
            background: #0284c7;
            color: #ffffff;
        }

        .btn-print:hover,
        .btn-print:active {
            background: #0369a1;
        }

        /* WRAPPER STRUK THERMAL DI LAYAR */
        .receipt-wrapper {
            width: 72mm;
            margin: 20px auto 40px;
            background: #fff;
            padding: 3mm;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
            border-radius: 4px;
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
            margin-bottom: 6px;
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
            font-size: 12pt;
            line-height: 1.25;
        }

        .summary-row.total {
            border-top: 1px dashed #000;
            margin-top: 4px;
            padding-top: 4px;
            font-size: 14pt;
        }

        .summary-label {
            max-width: 58%;
        }

        .summary-value {
            text-align: right;
            white-space: nowrap;
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

            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 2mm !important;
                width: 72mm !important;
            }

            .receipt-wrapper {
                width: 72mm !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: transparent !important;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <!-- ACTION BAR DI LAYAR (TIDAK TERCETAK DI KERTAS THERMAL) -->
    <div class="action-bar no-print">
        <button type="button" class="btn-action btn-back" onclick="handleKembali()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Transaksi
        </button>
        <button type="button" class="btn-action btn-print" onclick="window.print()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Cetak Ulang
        </button>
    </div>

    <div class="receipt-wrapper">
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
            <?php if ((string) ($transaksi->status ?? '') === 'Dibatalkan') : ?>
                <div class="info-row">
                    <span>Status: DIBATALKAN</span>
                </div>
                <div style="font-size: 9pt; line-height: 1.3;">
                    Alasan: <?= htmlspecialchars($transaksi->alasan_batal ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
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
                                    <?= htmlspecialchars($d->nama_paket, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($d->item_note_text) && trim((string) $d->item_note_text) !== '-') : ?>
                                <div style="font-size: 9pt; font-weight: normal;">
                                    <?= htmlspecialchars($d->item_note_text, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="qty"><?= $d->qty_label; ?><?= strtolower($unit_label); ?></td>
                        <td class="price"><?= number_format($subtotal, 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary">
            <?php if (!empty($ringkasan_pembayaran['reward_tersedia'])) : ?>
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">Rp <?= number_format((float) ($ringkasan_pembayaran['total_normal'] ?? 0), 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Reward Member</span>
                    <span class="summary-value">- Rp <?= number_format((int) ($ringkasan_pembayaran['reward_potongan'] ?? 0), 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row total">
                    <span class="summary-label">TOTAL BAYAR</span>
                    <span class="summary-value">Rp <?= number_format((float) ($ringkasan_pembayaran['total_akhir'] ?? 0), 0, ',', '.'); ?></span>
                </div>
            <?php elseif (!empty($ringkasan_pembayaran['promo_tersedia'])) : ?>
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">Rp <?= number_format((float) ($ringkasan_pembayaran['total_normal'] ?? 0), 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Promo <?= rtrim(rtrim(number_format((float) ($ringkasan_pembayaran['promo_gratis_qty'] ?? 0), 2, '.', ''), '0'), '.'); ?> <?= htmlspecialchars($ringkasan_pembayaran['promo_gratis_unit'] ?? 'kg', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="summary-value">- Rp <?= number_format((int) ($ringkasan_pembayaran['promo_gratis_potongan'] ?? 0), 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row total">
                    <span class="summary-label">TOTAL BAYAR</span>
                    <span class="summary-value">Rp <?= number_format((float) ($ringkasan_pembayaran['total_akhir'] ?? 0), 0, ',', '.'); ?></span>
                </div>
            <?php else : ?>
                <div class="summary-row total">
                    <span class="summary-label">TOTAL BAYAR</span>
                    <span class="summary-value">Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="status-bayar">
            <?= (string) ($transaksi->status ?? '') === 'Dibatalkan' ? 'DIBATALKAN' : strtoupper($transaksi->dibayar); ?>
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
    </div> <!-- /.receipt-wrapper -->

    <script>
        function handleKembali() {
            var fallbackUrl = "<?= base_url('transaksi/detail/' . $transaksi->kode_invoice); ?>";

            // 1. Jika dibuka di popup / tab baru yang bisa ditutup
            if (window.opener && !window.opener.closed) {
                window.close();
                // Jika browser memblokir window.close()
                setTimeout(function() {
                    window.location.href = fallbackUrl;
                }, 200);
                return;
            }

            // 2. Jika ada riwayat halaman sebelumnya (PWA atau tab yang sama)
            if (window.history.length > 1) {
                window.history.back();
                setTimeout(function() {
                    window.location.href = fallbackUrl;
                }, 300);
                return;
            }

            // 3. Fallback pasti: arahkan langsung ke halaman detail transaksi
            window.location.href = fallbackUrl;
        }
    </script>
</body>

</html>
