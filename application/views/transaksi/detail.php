<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <style>
        .trx-detail-table {
            min-width: 940px;
        }

        .trx-detail-table .col-paket {
            width: 38%;
        }

        .trx-detail-table .col-harga {
            width: 13%;
        }

        .trx-detail-table .col-qty {
            width: 9%;
        }

        .trx-detail-table .col-catatan {
            width: 20%;
        }

        .trx-detail-table .col-subtotal {
            width: 20%;
        }

        .trx-detail-table .paket-cell {
            min-width: 320px;
        }

        .trx-detail-table .paket-title {
            font-size: 1rem;
            line-height: 1.35;
        }

        .trx-detail-table .paket-meta {
            font-size: 0.8rem;
            line-height: 1.45;
        }

        .trx-detail-table .catatan-cell {
            min-width: 180px;
        }

        .trx-detail-table .catatan-text {
            line-height: 1.45;
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">&nbsp;</h1>
        <div>
            <a href="<?= base_url('transaksi'); ?>" class="btn btn-secondary btn-sm me-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <a href="<?= base_url('transaksi/cetak/' . $transaksi->kode_invoice); ?>" target="_blank" class="btn btn-success btn-sm">
                <i class="fas fa-print me-1"></i> Cetak Invoice
            </a>
        </div>
    </div>

    <div class="flash-data-success" data-flashdata="<?= $this->session->flashdata('success'); ?>"></div>

    <div class="row">

        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="fas fa-info-circle me-2"></i> Info Invoice</span>
                    <span class="badge bg-light text-primary"><?= $transaksi->kode_invoice; ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nama Pelanggan</small>
                            <h6 class="fw-bold"><?= $transaksi->nama_pelanggan; ?></h6>
                            <a href="https://wa.me/62<?= $transaksi->no_hp; ?>" target="_blank" class="text-success text-decoration-none small">
                                <i class="fab fa-whatsapp me-1"></i> Hubungi via WA
                            </a>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small class="text-muted d-block">Tanggal Masuk</small>
                            <span class="fw-bold"><?= date('d/m/Y H:i', strtotime($transaksi->tgl_masuk)); ?></span>
                            <small class="text-muted d-block mt-2">Batas Waktu</small>
                            <span class="text-danger fw-bold"><?= date('d/m/Y H:i', strtotime($transaksi->batas_waktu)); ?></span>
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3">Rincian Paket Laundry</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle trx-detail-table">
                            <colgroup>
                                <col class="col-paket">
                                <col class="col-harga">
                                <col class="col-qty">
                                <col class="col-catatan">
                                <col class="col-subtotal">
                            </colgroup>
                            <thead class="table-light">
                                <tr>
                                    <th>Paket</th>
                                    <th>Harga</th>
                                    <th class="text-center">Qty</th>
                                    <th>Catatan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $grand_total = 0;
                                foreach ($detail as $d) :
                                    $subtotal = $d->subtotal;
                                    $grand_total += $subtotal;
                                    $item_label = !empty($d->nama_tipe) ? $d->nama_tipe : $d->nama_paket;
                                ?>
                                    <tr>
                                        <td class="paket-cell">
                                            <span class="fw-semibold d-block paket-title"><?= htmlspecialchars($item_label, ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php if (!empty($d->nama_paket) && strcasecmp($d->nama_paket, $item_label) !== 0) : ?>
                                                <small class="d-block text-muted mt-1 paket-meta">
                                                    Paket: <?= htmlspecialchars($d->nama_paket, ENT_QUOTES, 'UTF-8'); ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (!empty($d->promo_applied)) : ?>
                                                <small class="d-block text-primary mt-2 paket-meta">
                                                    <i class="fas fa-tags me-1"></i><?= $d->promo_label; ?>:
                                                    berat asli <?= $d->qty_label; ?> kg, dibulatkan <?= (float) $d->rounded_qty; ?> kg,
                                                    dibayar <?= (float) $d->charged_qty; ?> kg.
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>Rp <?= number_format($d->harga, 0, ',', '.'); ?></td>
                                        <td class="text-center"><?= $d->qty_label; ?><?= !empty($d->promo_applied) ? ' kg' : ''; ?></td>
                                        <td class="catatan-cell">
                                            <?php if ($transaksi->status === 'Baru') : ?>
                                                <form action="<?= base_url('transaksi/update_catatan_item'); ?>" method="post">
                                                    <input type="hidden" name="kode_invoice" value="<?= $transaksi->kode_invoice; ?>">
                                                    <input type="hidden" name="detail_id" value="<?= $d->id; ?>">
                                                    <textarea name="customer_notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Contoh: celana 2 pcs, baju 3 pcs"><?= htmlspecialchars($d->customer_notes ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-save me-1"></i> Simpan Catatan
                                                    </button>
                                                </form>
                                            <?php else : ?>
                                                <?php if (!empty($d->customer_notes)) : ?>
                                                    <span class="text-muted small catatan-text"><?= nl2br(htmlspecialchars($d->customer_notes, ENT_QUOTES, 'UTF-8')); ?></span>
                                                <?php else : ?>
                                                    <span class="text-muted small catatan-text">Tidak ada catatan.</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end text-nowrap">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">TOTAL HARUS DIBAYAR</td>
                                    <td class="text-end fw-bold fs-5 text-primary text-nowrap">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-tshirt me-2"></i> Status Laundry
                </div>
                <div class="card-body">
                    <form action="<?= base_url('transaksi/update_status'); ?>" method="post">
                        <input type="hidden" name="kode_invoice" value="<?= $transaksi->kode_invoice; ?>">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Update Status Pengerjaan</label>
                            <select name="status" class="form-select">
                                <?php foreach ($status_options as $value => $label) : ?>
                                    <option value="<?= $value; ?>" <?= $transaksi->status == $value ? 'selected' : ''; ?>><?= $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-sm">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-money-bill-wave me-2"></i> Pembayaran
                </div>
                <div class="card-body text-center">

                    <?php if ($transaksi->dibayar == 'Belum Dibayar') : ?>
                        <div class="alert alert-danger mb-3">
                            Status: <strong>BELUM LUNAS</strong>
                        </div>

                        <form id="formBayar" action="<?= base_url('transaksi/bayar_tagihan/' . $transaksi->kode_invoice); ?>" method="post">
                            <div class="mb-3 text-start">
                                <label class="form-label small text-muted fw-bold">Pilih Metode Pembayaran</label>
                                <?php foreach ($metode_bayar as $mb) : ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="id_metode_bayar" id="metode_<?= $mb->id; ?>" value="<?= $mb->id; ?>" <?= $mb->nama == 'Tunai' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="metode_<?= $mb->id; ?>">
                                            <?php if ($mb->nama == 'Tunai') : ?>
                                                <i class="fas fa-money-bill-wave text-success me-1"></i>
                                            <?php elseif ($mb->nama == 'QRIS') : ?>
                                                <i class="fas fa-qrcode text-primary me-1"></i>
                                            <?php elseif ($mb->nama == 'Transfer') : ?>
                                                <i class="fas fa-university text-info me-1"></i>
                                            <?php endif; ?>
                                            <?= $mb->nama; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <p class="small text-muted">Pastikan uang sudah diterima sebelum konfirmasi.</p>

                            <button type="button" class="btn btn-success w-100 btn-bayar">
                                <i class="fas fa-check-circle me-2"></i> Bayar Sekarang
                            </button>
                        </form>

                    <?php else : ?>
                        <div class="alert alert-success mb-3 text-center">
                            Status: <strong>LUNAS</strong>
                        </div>
                        <?php if (!empty($transaksi->nama_metode_bayar)) : ?>
                            <small class="text-muted d-block">Metode Bayar:</small>
                            <span class="fw-bold">
                                <?php if ($transaksi->nama_metode_bayar == 'Tunai') : ?>
                                    <i class="fas fa-money-bill-wave text-success me-1"></i>
                                <?php elseif ($transaksi->nama_metode_bayar == 'QRIS') : ?>
                                    <i class="fas fa-qrcode text-primary me-1"></i>
                                <?php elseif ($transaksi->nama_metode_bayar == 'Transfer') : ?>
                                    <i class="fas fa-university text-info me-1"></i>
                                <?php endif; ?>
                                <?= $transaksi->nama_metode_bayar; ?>
                            </span>
                        <?php endif; ?>
                        <small class="text-muted d-block mt-2">Dibayar pada:</small>
                        <span class="fw-bold"><?= date('d/m/Y H:i', strtotime($transaksi->tgl_bayar)); ?></span>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</main>

<script>
    // SweetAlert untuk Konfirmasi Bayar (submit form POST)
    document.addEventListener('DOMContentLoaded', function() {
        var btnBayar = document.querySelector('.btn-bayar');
        if (btnBayar) {
            btnBayar.addEventListener('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Konfirmasi Pembayaran',
                    text: "Apakah pelanggan sudah membayar tagihan lunas?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Sudah Bayar!'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        document.getElementById('formBayar').submit();
                    }
                });
            });
        }
    });
</script>
