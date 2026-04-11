<?php if (validation_errors()) : ?>
    <div class="flash-data-error" data-flashdata="<?= str_replace(array("\r", "\n"), '', nl2br(validation_errors())); ?>"></div>
<?php endif; ?>

<div class="container-fluid p-4">
    <div class="row">
        <div class="col-md-8 mx-auto">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary">
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
                                <label for="id_kategori" class="form-label fw-bold">Kategori Layanan</label>
                                <select class="form-select" id="id_kategori" name="id_kategori" required>
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <?php foreach($kategori as $kat) : ?>
                                        <option value="<?= $kat->id_kategori ?>" <?= ($paket->id_kategori ?? '') == $kat->id_kategori ? 'selected' : ''; ?>>
                                            <?= $kat->nama_kategori ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_satuan" class="form-label fw-bold">Satuan Ukuran</label>
                                <select class="form-select" id="id_satuan" name="id_satuan" required>
                                    <option value="" disabled selected>-- Pilih Satuan --</option>
                                    <?php foreach($satuan as $sat) : ?>
                                        <option value="<?= $sat->id_satuan ?>" <?= ($paket->id_satuan ?? '') == $sat->id_satuan ? 'selected' : ''; ?>>
                                            <?= $sat->nama_satuan ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

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
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        // 1. Script untuk Konfirmasi Simpan (Edit/Baru)
        $('.btn-simpan').on('click', function(e) {
            e.preventDefault(); // Mencegah form submit langsung

            Swal.fire({
                title: 'Simpan Data?',
                text: "Pastikan data yang diinput sudah benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754', // Warna hijau success
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik Ya, baru form di-submit secara manual
                    $('#formPaket').submit();
                }
            });
        });

        // 2. Script untuk Menampilkan Error Validasi (Jika ada error dari Controller)
        const flashError = $('.flash-data-error').data('flashdata');
        if (flashError) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: flashError, // Pakai html biar <br> terbaca
            });
        }

    });
</script>