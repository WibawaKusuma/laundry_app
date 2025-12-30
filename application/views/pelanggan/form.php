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
                    $url_action = empty($pelanggan->id) ? base_url('pelanggan/simpan') : base_url('pelanggan/update');
                    ?>

                    <form action="<?= $url_action; ?>" method="post">

                        <input type="hidden" name="id" value="<?= $pelanggan->id ?? ''; ?>">

                        <div class="mb-3">
                            <label for="nama" class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= $pelanggan->nama ?? ''; ?>" placeholder="Nama Pelanggan" required>
                        </div>

                        <div class="mb-3">
                            <label for="no_hp" class="form-label fw-bold">No HP (WhatsApp)</label>
                            <div class="input-group">
                                <span class="input-group-text">+62</span>
                                <input type="number" class="form-control" id="no_hp" name="no_hp" value="<?= $pelanggan->no_hp ?? ''; ?>" placeholder="8123xxxx" required>
                            </div>
                            <small class="text-muted">Masukkan angka saja, tanpa 0 atau 62 di depan.</small>
                        </div>

                        <div class="mb-4">
                            <label for="alamat" class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Alamat domisili..."><?= $pelanggan->alamat ?? ''; ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= base_url('pelanggan'); ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Simpan Data
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('templates/footer'); ?>