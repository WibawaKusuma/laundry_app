<?php if (validation_errors()) : ?>
    <div class="flash-data-error" data-flashdata="<?= str_replace(array("\r", "\n"), '', nl2br(validation_errors())); ?>"></div>
<?php endif; ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="row">
        <div class="col-md-10 mx-auto">

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header app-section-header py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i> <?= $title; ?>
                    </h5>
                </div>

                <div class="card-body p-4">

                    <?php
                    $is_edit = !empty($paket->id_paket_laundry);
                    $url_action = $is_edit ? base_url('paket/update') : base_url('paket/simpan');
                    ?>

                    <form id="formPaket" action="<?= $url_action; ?>" method="post">

                        <?= form_hidden('id', $paket->id_paket_laundry ?? ''); ?>

                        <div class="mb-3">
                            <label for="nama_paket" class="form-label fw-bold">Nama Paket Laundry</label>
                            <input type="text" class="form-control" id="nama_paket" name="nama_paket"
                                placeholder="Contoh: Express atau Reguler"
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
                                <label for="id_kategori" class="form-label fw-bold">Kategori Layanan</label>
                                <select class="form-select" id="id_kategori" name="id_kategori" required>
                                    <option value="" disabled <?= empty($paket->id_kategori) ? 'selected' : ''; ?>>-- Pilih Kategori --</option>
                                    <?php foreach ($kategori as $kat) : ?>
                                        <option value="<?= $kat->id_kategori ?>" <?= ($paket->id_kategori ?? '') == $kat->id_kategori ? 'selected' : ''; ?>>
                                            <?= $kat->nama_kategori ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_tipe" class="form-label fw-bold">Tipe Laundry</label>
                                <select class="form-select" id="id_tipe" name="id_tipe" required>
                                    <option value="" disabled <?= empty($paket->id_tipe) ? 'selected' : ''; ?>>-- Pilih Tipe --</option>
                                    <?php foreach ($tipe as $tp) : ?>
                                        <option value="<?= $tp->id_tipe ?>" <?= ($paket->id_tipe ?? '') == $tp->id_tipe ? 'selected' : ''; ?>>
                                            <?= $tp->nama_tipe ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="id_satuan" class="form-label fw-bold">Satuan Ukuran</label>
                                <select class="form-select" id="id_satuan" name="id_satuan" required>
                                    <option value="" disabled <?= empty($paket->id_satuan) ? 'selected' : ''; ?>>-- Pilih Satuan --</option>
                                    <?php foreach ($satuan as $sat) : ?>
                                        <option value="<?= $sat->id_satuan ?>" <?= ($paket->id_satuan ?? '') == $sat->id_satuan ? 'selected' : ''; ?>>
                                            <?= $sat->nama_satuan ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="durasi_jam" class="form-label fw-bold">Estimasi Durasi Pengerjaan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="durasi_jam" name="durasi_jam"
                                        placeholder="Contoh: 24"
                                        value="<?= $paket->durasi_jam ?? ''; ?>" required>
                                    <span class="input-group-text bg-light">Jam</span>
                                </div>
                                <small class="text-muted">Ketik angka jam saja (Contoh: 24 jam = 1 hari).</small>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= base_url('paket'); ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-sm btn-success btn-simpan">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        $('.btn-simpan').on('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Simpan Data?',
                text: 'Pastikan data yang diinput sudah benar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#formPaket').submit();
                }
            });
        });

        const flashError = $('.flash-data-error').data('flashdata');
        if (flashError) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: flashError,
            });
        }
    });
</script>
