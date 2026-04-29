<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <?php $promo_enabled = !empty($promo_settings['is_enabled']); ?>

    <style>
        .trx-payment-summary {
            border: 1px solid rgba(31, 41, 122, 0.12);
            border-radius: 18px;
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
            background: linear-gradient(180deg, #f8faff 0%, #eef3ff 100%);
            box-shadow: 0 10px 26px rgba(31, 41, 122, 0.08);
        }

        .trx-payment-summary-label {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            color: #58627f;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.45rem;
        }

        .trx-payment-summary-value {
            color: #1f297a;
            /* font-size: clamp(1.8rem, 2.6vw, 2.25rem); */
            font-size: clamp(1.2rem, 2vw, 1.6rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.03em;
        }

        .trx-payment-summary-note {
            margin-top: 0.45rem;
            color: #69738f;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .trx-payment-status {
            border-radius: 16px;
            margin-bottom: 1rem;
        }

        .trx-method-list {
            display: grid;
            gap: 0.75rem;
        }

        .trx-method-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid #dbe3f3;
            border-radius: 14px;
            padding: 0.8rem 0.9rem;
            transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
        }

        .trx-method-option:hover {
            background: #f8faff;
            border-color: rgba(31, 41, 122, 0.24);
        }

        .trx-method-option:focus-within {
            background: #f8faff;
            border-color: #1f297a;
            box-shadow: 0 0 0 0.2rem rgba(31, 41, 122, 0.12);
        }

        .trx-method-option .form-check-input {
            margin-top: 0;
            flex-shrink: 0;
        }

        .trx-method-option .form-check-input:checked {
            background-color: #1f297a;
            border-color: #1f297a;
        }

        .trx-method-option .form-check-label {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            margin-bottom: 0;
            width: 100%;
            color: #24304f;
            font-weight: 600;
        }

        .trx-payment-hint {
            color: #667085;
            font-size: 0.88rem;
            line-height: 1.5;
            margin: 0.95rem 0 0.9rem;
        }

        .trx-pay-button {
            min-height: 48px;
            /* border-radius: 14px; */
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(25, 135, 84, 0.18);
        }

        .trx-detail-table {
            min-width: 860px;
        }

        .trx-detail-table .col-aksi {
            width: 9%;
        }

        .trx-detail-table .col-paket {
            width: 24%;
        }

        .trx-detail-table .col-harga {
            width: 16%;
        }

        .trx-detail-table .col-qty {
            width: 9%;
        }

        .trx-detail-table .col-catatan {
            width: 18%;
        }

        .trx-detail-table .col-subtotal {
            width: 24%;
        }

        .trx-detail-table .paket-cell {
            min-width: 220px;
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
            min-width: 140px;
        }

        .trx-detail-table .catatan-text {
            line-height: 1.45;
        }

        .trx-detail-action-stack {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .trx-detail-action-stack form {
            margin: 0;
        }


        .trx-detail-edit-modal .modal-content {
            border: 1px solid rgba(31, 41, 122, 0.12);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 42px rgba(31, 41, 122, 0.16);
        }

        .trx-detail-edit-modal .modal-header {
            background: linear-gradient(135deg, #f7f9ff 0%, #eef3ff 100%);
            border-bottom: 1px solid rgba(31, 41, 122, 0.08);
            padding: 1rem 1.25rem;
        }

        .trx-detail-edit-modal .modal-title {
            color: #1f297a;
        }

        .trx-detail-edit-modal .modal-body {
            padding: 1.2rem 1.25rem 1.35rem;
        }

        .trx-detail-edit-modal .modal-footer {
            border-top: 1px solid rgba(31, 41, 122, 0.08);
            padding: 1rem 1.25rem 1.15rem;
        }

        .trx-detail-edit-modal .form-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #46506f;
            margin-bottom: 0.4rem;
        }

        .trx-detail-edit-modal .form-control {
            border-color: #d8def0;
        }

        .trx-detail-edit-modal .form-control:focus {
            border-color: #1f297a;
            box-shadow: 0 0 0 0.2rem rgba(31, 41, 122, 0.12);
        }

        .trx-detail-edit-meta {
            color: #5f688b;
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .trx-detail-edit-summary {
            border: 1px solid rgba(31, 41, 122, 0.1);
            border-radius: 16px;
            background: #f8faff;
            padding: 0.95rem 1rem;
        }

        .customer-wa-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            white-space: nowrap;
        }

        .customer-wa-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-size: 0.8125rem;
            line-height: 1.2;
            padding: 0.4rem 0.75rem;
            flex: 0 0 auto;
        }

        @media (max-width: 1199.98px) {
            .customer-wa-actions {
                gap: 0.4rem;
            }

            .customer-wa-actions .btn {
                font-size: 0.76rem;
                padding: 0.38rem 0.65rem;
            }
        }

        .trx-add-item-card {
            border: 1px solid rgba(31, 41, 122, 0.1);
            background: #fbfcff;
            box-shadow: 0 10px 30px rgba(31, 41, 122, 0.05);
        }

        .trx-add-item-title {
            color: #1f297a;
        }

        .trx-add-item-hint {
            font-size: 0.82rem;
            color: #5f688b;
            line-height: 1.5;
        }

        .trx-add-item-card .form-label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #344054;
            margin-bottom: 0.45rem;
        }

        .trx-add-item-card .form-control,
        .trx-add-item-card .form-select {
            border-color: #d8def0;
            min-height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            background: #fff;
        }

        .trx-add-item-card .form-control:focus,
        .trx-add-item-card .form-select:focus {
            border-color: #1f297a;
            box-shadow: 0 0 0 0.2rem rgba(31, 41, 122, 0.12);
        }

        .trx-add-item-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            border-radius: 999px;
            padding-inline: 1rem;
        }

        .trx-add-item-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .trx-add-item-panel {
            border: 1px solid #e6ebf7;
            border-radius: 0.75rem;
            padding: 1rem;
            background: #fff;
            min-height: 100%;
        }

        .trx-add-item-submit {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.25rem;
        }

        .trx-add-item-note {
            font-size: 0.8rem;
            color: #667085;
        }

        .trx-lock-note {
            border: 1px dashed rgba(31, 41, 122, 0.18);
            background: #f8f9fd;
            color: #4f5b86;
            border-radius: 16px;
            padding: 0.9rem 1rem;
        }

        .trx-add-item-card textarea.form-control {
            min-height: 76px;
            resize: vertical;
        }

        @media (max-width: 991.98px) {
            .trx-add-item-head {
                flex-direction: column;
            }
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
    <div class="flash-data-error" data-flashdata="<?= $this->session->flashdata('error'); ?>"></div>

    <div class="row">

        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4 app-section-card">
                <div class="card-header app-section-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="fas fa-info-circle me-2 app-section-header-icon"></i> Info Invoice</span>
                    <span class="badge bg-light text-primary"><?= $transaksi->kode_invoice; ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nama Pelanggan</small>
                            <h6 class="fw-bold"><?= $transaksi->nama_pelanggan; ?></h6>
                            <?php if (!empty($wa_contact_link) || !empty($wa_confirmation_link)) : ?>
                                <div class="customer-wa-actions">
                                    <?php if (!empty($wa_contact_link)) : ?>
                                        <a href="<?= $wa_contact_link; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="fab fa-whatsapp me-1"></i> Hubungi via WA
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($wa_confirmation_link)) : ?>
                                        <a href="<?= $wa_confirmation_link; ?>" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-receipt me-1"></i> Kirim Nota
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
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
                                <col class="col-aksi">
                                <col class="col-paket">
                                <col class="col-harga">
                                <col class="col-qty">
                                <col class="col-catatan">
                                <col class="col-subtotal">
                            </colgroup>
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Aksi</th>
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
                                foreach (($active_detail ?? $detail) as $d) :
                                    $subtotal = $d->subtotal;
                                    $grand_total += $subtotal;
                                    $item_label = !empty($d->nama_tipe) ? $d->nama_tipe : $d->nama_paket;
                                    $unit_label = strtoupper($d->nama_satuan ?? '');
                                ?>
                                    <tr>
                                        <td class="text-center text-nowrap">
                                            <?php if (!empty($can_modify_items)) : ?>
                                                <div class="trx-detail-action-stack">
                                                    <button
                                                        class="btn btn-sm btn-outline-primary js-edit-detail"
                                                        type="button"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditDetailItem"
                                                        data-detail-id="<?= (int) $d->id; ?>"
                                                        data-item-label="<?= htmlspecialchars($item_label, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-paket-label="<?= htmlspecialchars($d->nama_paket ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-qty="<?= htmlspecialchars($d->qty_label, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-unit="<?= htmlspecialchars($unit_label, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-customer-notes="<?= htmlspecialchars($d->customer_notes ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-harga="<?= number_format($d->harga, 0, ',', '.'); ?>"
                                                        data-promo-applied="<?= !empty($d->promo_applied) ? '1' : '0'; ?>"
                                                        data-promo-label="<?= htmlspecialchars($d->promo_label ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fas fa-pen me-1"></i>
                                                    </button>
                                                    <form action="<?= base_url('transaksi/batal_detail_item'); ?>" method="post" class="js-cancel-detail-form">
                                                        <input type="hidden" name="kode_invoice" value="<?= $transaksi->kode_invoice; ?>">
                                                        <input type="hidden" name="detail_id" value="<?= (int) $d->id; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash-can me-1"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else : ?>
                                                <span class="text-muted small">Terkunci</span>
                                            <?php endif; ?>
                                        </td>
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
                                            <?php if (!empty($d->customer_notes)) : ?>
                                                <span class="text-muted small catatan-text"><?= nl2br(htmlspecialchars($d->customer_notes, ENT_QUOTES, 'UTF-8')); ?></span>
                                            <?php else : ?>
                                                <span class="text-muted small catatan-text">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end text-nowrap">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">TOTAL</td>
                                    <td class="text-end fw-bold fs-5 text-primary text-nowrap">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if (!empty($can_modify_items)) : ?>
                        <div class="modal fade trx-detail-edit-modal" id="modalEditDetailItem" tabindex="-1" aria-labelledby="modalEditDetailItemLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="<?= base_url('transaksi/update_detail_item'); ?>" method="post">
                                        <div class="modal-header">
                                            <div>
                                                <h5 class="modal-title fw-bold mb-1" id="modalEditDetailItemLabel">Edit Item Laundry</h5>
                                                <div class="small text-muted" id="modalEditDetailSubtitle">Perbarui qty dan catatan item yang dipilih.</div>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="kode_invoice" value="<?= $transaksi->kode_invoice; ?>">
                                            <input type="hidden" name="detail_id" id="modalEditDetailId" value="">

                                            <div class="trx-detail-edit-summary mb-3">
                                                <div class="fw-semibold text-dark mb-1" id="modalEditDetailItemLabelText">-</div>
                                                <div class="trx-detail-edit-meta" id="modalEditDetailPackageText">-</div>
                                                <div class="trx-detail-edit-meta mt-2" id="modalEditDetailPricingText">Harga satuan: -</div>
                                                <div class="trx-detail-edit-meta mt-2" id="modalEditDetailPromoText">Subtotal akan menyesuaikan qty terbaru.</div>
                                            </div>

                                            <div class="row g-3 align-items-start">
                                                <div class="col-md-4">
                                                    <label class="form-label" for="modalEditDetailQty">Jumlah Bawaan</label>
                                                    <input type="number" name="qty" id="modalEditDetailQty" class="form-control" min="0.1" step="0.01" required>
                                                    <div class="trx-detail-edit-meta mt-2" id="modalEditDetailUnitText">Satuan item ini: -.</div>
                                                </div>

                                                <div class="col-md-8">
                                                    <label class="form-label" for="modalEditDetailNotes">Catatan Barang Bawaan</label>
                                                    <textarea name="customer_notes" id="modalEditDetailNotes" class="form-control" rows="2" placeholder="Contoh: celana 2 pcs, baju 3 pcs"></textarea>
                                                    <!-- <div class="trx-detail-edit-meta mt-2">Perubahan disimpan per item berdasarkan ID detail transaksi.</div> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save me-1"></i>Simpan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($can_add_items) : ?>
                        <div class="mt-4 pt-2 border-top">
                            <button class="btn btn-sm btn-outline-primary trx-add-item-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTambahItem" aria-expanded="false" aria-controls="collapseTambahItem">
                                <i class="fas fa-plus-circle"></i>
                                <span>Tambah Item Laundry</span>
                            </button>

                            <div class="collapse mt-3" id="collapseTambahItem">
                                <div class="trx-add-item-card rounded-4 p-3 p-lg-4">
                                    <div class="trx-add-item-head">
                                        <div>
                                            <h6 class="fw-bold mb-1 trx-add-item-title">Koreksi Item yang Tertinggal</h6>
                                            <p class="mb-0 trx-add-item-hint">
                                                Gunakan form ini jika ada item pelanggan yang tadi belum sempat diinput. Item baru tetap masuk ke nota yang sama dan isi tombol Kirim Nota akan ikut diperbarui.
                                            </p>
                                        </div>
                                        <span class="badge rounded-pill text-bg-light border text-primary px-3 py-2">Hanya saat status masih Baru</span>
                                    </div>

                                    <form action="<?= base_url('transaksi/tambah_item/' . $transaksi->kode_invoice); ?>" method="post">
                                        <div class="row g-3 align-items-start">
                                            <div class="col-md-6 col-xl-4">
                                                <label class="form-label">Kategori Layanan</label>
                                                <select id="detail_filter_kategori" class="form-select">
                                                    <option value="">-- Semua Kategori --</option>
                                                    <?php foreach ($kategori as $kat) : ?>
                                                        <option value="<?= $kat->id_kategori; ?>"><?= $kat->nama_kategori; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6 col-xl-4">
                                                <label class="form-label">Tipe Laundry</label>
                                                <select id="detail_filter_tipe" class="form-select">
                                                    <option value="">-- Semua Tipe --</option>
                                                    <?php foreach ($tipe as $tp) : ?>
                                                        <option value="<?= $tp->id_tipe; ?>"><?= $tp->nama_tipe; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6 col-xl-4">
                                                <label class="form-label">Paket Laundry</label>
                                                <select name="id_paket" id="detail_id_paket" class="form-select" required>
                                                    <option value="">-- Pilih Paket --</option>
                                                    <?php foreach ($paket as $pk) : ?>
                                                        <option value="<?= $pk->id_paket_laundry; ?>"
                                                            data-kategori="<?= $pk->id_kat; ?>"
                                                            data-tipe="<?= $pk->id_tp; ?>">
                                                            <?= $pk->nama_tipe; ?> - <?= $pk->nama_paket; ?> - Rp <?= number_format($pk->harga, 0, ',', '.'); ?> / <?= strtoupper($pk->nama_satuan ?? '-'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="text-muted d-none" id="detail_info_paket_kosong">Tidak ada paket untuk kombinasi kategori dan tipe ini.</small>
                                            </div>

                                            <div class="col-md-6 col-xl-4">
                                                <label class="form-label">Jumlah Bawaan (Qty)</label>
                                                <input type="number" name="qty" class="form-control" value="" min="0.1" step="0.01" placeholder="Contoh: 1.5 atau 2" required>
                                            </div>

                                            <div class="col-md-6 col-xl-8">
                                                <label class="form-label">Catatan Barang Bawaan</label>
                                                <textarea name="customer_notes" class="form-control" rows="3" placeholder="Opsional. Contoh: rok 2 pcs, selimut 1, baju putih dipisah"></textarea>
                                                <small class="text-muted">Catatan item ini masih bisa diedit selama transaksi tetap berstatus Baru.</small>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">Promo Transaksi</label>
                                                <div class="trx-add-item-panel">
                                                    <?php if ($promo_enabled) : ?>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" value="1" id="detail_promo_cuci_3kg" name="promo_cuci_3kg">
                                                            <label class="form-check-label fw-semibold" for="detail_promo_cuci_3kg">
                                                                Aktifkan <?= $promo_settings['label']; ?>
                                                            </label>
                                                        </div>
                                                        <small class="text-muted d-block">
                                                            Berlaku untuk layanan kiloan tipe Cuci Komplit dan Cuci Setrika. Berat dibulatkan ke atas lalu <?= $promo_settings['free_qty']; ?> kg pertama gratis.
                                                        </small>
                                                        <small class="text-primary d-block mt-2">
                                                            Contoh: 3.8 kg dihitung 4 kg, yang dibayar hanya 1 kg. Layanan Setrika saja tidak mendapat promo.
                                                        </small>
                                                    <?php else : ?>
                                                        <small class="text-muted d-block">Promo tidak aktif untuk transaksi ini.</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="trx-add-item-submit">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus me-2"></i>Tambah ke Nota Ini
                                            </button>
                                            <span class="trx-add-item-note">Total nota dan isi pesan WhatsApp akan ikut diperbarui.</span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="trx-lock-note mt-4">
                            <div class="fw-semibold mb-1"><i class="fas fa-lock me-2"></i>Tambah Item Dinonaktifkan</div>
                            <div class="small mb-0"><?= htmlspecialchars($add_item_block_reason, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">

            <div class="card shadow-sm border-0 mb-3 app-section-card">
                <div class="card-header app-section-header fw-bold">
                    <i class="fas fa-tshirt me-2 app-section-header-icon"></i> Status Laundry
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
                        <button type="submit" class="btn btn-success w-100 btn-sm">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 app-section-card">
                <div class="card-header app-section-header fw-bold">
                    <i class="fas fa-money-bill-wave me-2 app-section-header-icon"></i> Pembayaran
                </div>
                <div class="card-body text-center">
                    <div class="trx-payment-summary">
                        <div class="trx-payment-summary-label">
                            <i class="fas fa-receipt"></i>
                            <span>Total Harus Dibayar</span>
                        </div>
                        <div class="trx-payment-summary-value">Rp <?= number_format($grand_total, 0, ',', '.'); ?></div>
                        <div class="trx-payment-summary-note">
                            Nominal ini harus cocok!.
                        </div>
                    </div>

                    <?php if ($transaksi->dibayar == 'Belum Dibayar') : ?>
                        <div class="alert alert-danger trx-payment-status">
                            Status: <strong>BELUM LUNAS</strong>
                        </div>

                        <form id="formBayar" action="<?= base_url('transaksi/bayar_tagihan/' . $transaksi->kode_invoice); ?>" method="post">
                            <div class="mb-3 text-start">
                                <label class="form-label small text-muted fw-bold">Pilih Metode Pembayaran</label>
                                <div class="trx-method-list">
                                    <?php foreach ($metode_bayar as $mb) : ?>
                                        <div class="form-check trx-method-option mb-0">
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
                            </div>

                            <!-- <p class="trx-payment-hint">Pastikan uang sudah diterima penuh sesuai total tagihan sebelum konfirmasi.</p> -->

                            <button
                                type="button"
                                class="btn btn-sm btn-success w-100 btn-bayar trx-pay-button"
                                data-total="Rp <?= number_format($grand_total, 0, ',', '.'); ?>">
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
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.jQuery === 'undefined' || !document.getElementById('detail_id_paket')) {
            return;
        }

        const $ = window.jQuery;
        const $paket = $('#detail_id_paket');
        const semuaOpsiPaket = $paket.find('option').not(':first').clone();

        function initSelect2PaketDetail() {
            $paket.select2({
                theme: 'bootstrap-5',
                placeholder: 'Ketik untuk mencari paket...',
                allowClear: true,
                width: '100%'
            });
        }

        function filterPaketDetail() {
            const selectedKat = $('#detail_filter_kategori').val();
            const selectedTipe = $('#detail_filter_tipe').val();

            if ($paket.hasClass('select2-hidden-accessible')) {
                $paket.select2('destroy');
            }

            $paket.find('option:not(:first)').remove();
            $paket.val('');

            const filtered = semuaOpsiPaket.filter(function() {
                const cocokKategori = selectedKat === '' || $(this).data('kategori') == selectedKat;
                const cocokTipe = selectedTipe === '' || $(this).data('tipe') == selectedTipe;
                return cocokKategori && cocokTipe;
            });

            if (filtered.length > 0) {
                $paket.append(filtered.clone());
                $('#detail_info_paket_kosong').addClass('d-none');
            } else {
                $('#detail_info_paket_kosong').removeClass('d-none');
            }

            initSelect2PaketDetail();
        }

        initSelect2PaketDetail();
        $('#detail_filter_kategori').on('change', filterPaketDetail);
        $('#detail_filter_tipe').on('change', filterPaketDetail);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('modalEditDetailItem');
        if (!modalEl) {
            return;
        }

        var detailIdInput = document.getElementById('modalEditDetailId');
        var qtyInput = document.getElementById('modalEditDetailQty');
        var notesInput = document.getElementById('modalEditDetailNotes');
        var itemLabelText = document.getElementById('modalEditDetailItemLabelText');
        var packageText = document.getElementById('modalEditDetailPackageText');
        var pricingText = document.getElementById('modalEditDetailPricingText');
        var promoText = document.getElementById('modalEditDetailPromoText');
        var unitText = document.getElementById('modalEditDetailUnitText');
        var subtitleText = document.getElementById('modalEditDetailSubtitle');

        document.querySelectorAll('.js-edit-detail').forEach(function(button) {
            button.addEventListener('click', function() {
                var detailId = button.getAttribute('data-detail-id') || '';
                var itemLabel = button.getAttribute('data-item-label') || '-';
                var paketLabel = button.getAttribute('data-paket-label') || '';
                var qty = button.getAttribute('data-qty') || '';
                var unit = button.getAttribute('data-unit') || '-';
                var customerNotes = button.getAttribute('data-customer-notes') || '';
                var harga = button.getAttribute('data-harga') || '-';
                var promoApplied = button.getAttribute('data-promo-applied') === '1';
                var promoLabel = button.getAttribute('data-promo-label') || 'Promo';

                detailIdInput.value = detailId;
                qtyInput.value = qty;
                notesInput.value = customerNotes;
                itemLabelText.textContent = itemLabel;
                packageText.textContent = paketLabel && paketLabel !== itemLabel ? 'Paket: ' + paketLabel : 'Paket detail mengikuti item yang dipilih.';
                pricingText.textContent = 'Harga satuan: Rp ' + harga;
                unitText.textContent = 'Satuan item ini: ' + unit + '.';
                subtitleText.textContent = 'Perbarui qty dan catatan untuk item transaksi #' + detailId + '.';

                if (promoApplied) {
                    promoText.textContent = promoLabel + ' aktif. Total akan dihitung ulang otomatis saat qty diubah.';
                } else {
                    promoText.textContent = 'Item ini tanpa promo. Subtotal akan mengikuti qty terbaru.';
                }
            });
        });
    });
</script>

<script>
    // SweetAlert untuk Konfirmasi Bayar (submit form POST)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.js-cancel-detail-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Batalkan Item Ini?',
                    html: `
                        <div style="padding-top:.25rem;">
                            <div style="display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .8rem;border-radius:999px;background:#fff4f4;color:#b42318;font-size:.88rem;font-weight:700;border:1px solid #f5c2c7;">
                                <i class="fas fa-ban"></i>
                                <span>Item ini tidak akan dihitung ke total</span>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Kembali',
                    focusCancel: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        var btnBayar = document.querySelector('.btn-bayar');
        if (btnBayar) {
            btnBayar.addEventListener('click', function(e) {
                e.preventDefault();
                var metodeTerpilih = document.querySelector('input[name="id_metode_bayar"]:checked');
                var labelMetode = metodeTerpilih ? document.querySelector('label[for="' + metodeTerpilih.id + '"]').innerText.trim() : '-';
                var totalBayar = btnBayar.getAttribute('data-total') || '-';

                Swal.fire({
                    title: 'Konfirmasi Pembayaran',
                    html: `
                        <div style="padding-top:.25rem;">
                            <div style="font-size:.75rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#69738f;margin-bottom:.5rem;">
                                Total yang harus dibayar
                            </div>
                            <div style="font-size:2rem;font-weight:800;line-height:1.05;color:#1f297a;margin-bottom:.75rem;">
                                ${totalBayar}
                            </div>
                            <div style="display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .8rem;border-radius:999px;background:#f5f7ff;color:#334155;font-size:.9rem;font-weight:600;border:1px solid #dbe3f3;">
                                <span>Metode bayar:</span>
                                <span>${labelMetode}</span>
                            </div>
                            <div style="margin-top:1rem;color:#4b5563;line-height:1.6;">
                                Apakah pelanggan sudah membayar sesuai total tagihan ini?
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Sudah Bayar!',
                    cancelButtonText: 'Batal',
                    focusCancel: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        document.getElementById('formBayar').submit();
                    }
                });
            });
        }
    });
</script>
