<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

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
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Paket</th>
                                    <th>Harga</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
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
                                        <td><?= $d->nama_paket; ?></td>
                                        <td>Rp <?= number_format($d->harga, 0, ',', '.'); ?></td>
                                        <td class="text-center"><?= $d->qty; ?></td>
                                        <td class="text-end">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">TOTAL HARUS DIBAYAR</td>
                                    <td class="text-end fw-bold fs-5 text-primary">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-tshirt me-2"></i> Status Laundry
                </div>
                <div class="card-body">
                    <form action="<?= base_url('transaksi/update_status'); ?>" method="post">
                        <input type="hidden" name="kode_invoice" value="<?= $transaksi->kode_invoice; ?>">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Update Status Pengerjaan</label>
                            <select name="status" class="form-select">
                                <option value="Baru" <?= $transaksi->status == 'Baru' ? 'selected' : '' ?>>Baru Masuk</option>
                                <option value="Proses" <?= $transaksi->status == 'Proses' ? 'selected' : '' ?>>Sedang Dicuci</option>
                                <option value="Selesai" <?= $transaksi->status == 'Selesai' ? 'selected' : '' ?>>Selesai (Siap Ambil)</option>
                                <option value="Diambil" <?= $transaksi->status == 'Diambil' ? 'selected' : '' ?>>Sudah Diambil</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-sm">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-money-bill-wave me-2"></i> Pembayaran
                </div>
                <div class="card-body text-center">

                    <?php if ($transaksi->dibayar == 'Belum Dibayar') : ?>
                        <div class="alert alert-danger mb-3">
                            Status: <strong>BELUM LUNAS</strong>
                        </div>
                        <p class="small text-muted">Pastikan uang sudah diterima sebelum konfirmasi.</p>

                        <a href="<?= base_url('transaksi/bayar_tagihan/' . $transaksi->kode_invoice); ?>" class="btn btn-success w-100 btn-bayar">
                            <i class="fas fa-check-circle me-2"></i> Bayar Sekarang
                        </a>

                    <?php else : ?>
                        <div class="alert alert-success mb-3 text-center">
                            <!-- <i class="fas fa-check-circle fa-2x mb-2 d-block"></i> -->
                            Status: <strong>LUNAS</strong>
                        </div>
                        <small class="text-muted d-block">Dibayar pada:</small>
                        <span class="fw-bold"><?= date('d/m/Y H:i', strtotime($transaksi->tgl_bayar)); ?></span>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</main>

<script>
    // SweetAlert untuk Konfirmasi Bayar
    $('.btn-bayar').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            text: "Apakah pelanggan sudah membayar tagihan lunas?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Sudah Bayar!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.location.href = href;
            }
        })
    });
</script>