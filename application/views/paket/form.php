<?php $this->load->view('templates/header'); ?>

<?php if (validation_errors()) : ?>
    <div class="flash-data-error" data-flashdata="<?= str_replace(array("\r", "\n"), '', nl2br(validation_errors())); ?>"></div>
<?php endif; ?>

<div class="container-fluid p-4">
    <div class="row">
        <div class="col-md-8 mx-auto">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary fw-bold">
                        <i class="fas fa-edit me-2"></i> <?= $title; ?>
                    </h5>
                </div>

                <div class="card-body p-4">

                    <?php
                    $is_edit = !empty($paket->id);
                    $url_action = $is_edit ? base_url('paket/update') : base_url('paket/simpan');
                    ?>

                    <form action="<?= $url_action; ?>" method="post">

                        <?= form_hidden('id', $paket->id ?? ''); ?>

                        <div class="mb-3">
                            <label for="nama_paket" class="form-label fw-bold">Nama Paket Laundry</label>
                            <input type="text" class="form-control" id="nama_paket" name="nama_paket"
                                placeholder="Contoh: Cuci Komplit Wangi"
                                value="<?= $paket->nama_paket ?? ''; ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="harga" class="form-label fw-bold">Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" class="form-control" id="harga" name="harga"
                                        placeholder="0"
                                        value="<?= $paket->harga ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="jenis" class="form-label fw-bold">Jenis Hitungan</label>
                                <select class="form-select" id="jenis" name="jenis" required>
                                    <option value="" disabled selected>-- Pilih Jenis --</option>
                                    <option value="kiloan" <?= ($paket->jenis ?? '') == 'kiloan' ? 'selected' : ''; ?>>Kiloan (Per Kg)</option>
                                    <option value="satuan" <?= ($paket->jenis ?? '') == 'satuan' ? 'selected' : ''; ?>>Satuan (Per Pcs)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="durasi_jam" class="form-label fw-bold">Estimasi Durasi Pengerjaan</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="durasi_jam" name="durasi_jam"
                                    placeholder="Contoh: 24"
                                    value="<?= $paket->durasi_jam ?? ''; ?>" required>
                                <span class="input-group-text bg-light">Jam</span>
                            </div>
                            <small class="text-muted">Masukkan estimasi waktu dalam satuan jam (Contoh: 24 jam = 1 hari).</small>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= base_url('paket'); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Simpan Data
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php $this->load->view('templates/footer'); ?>