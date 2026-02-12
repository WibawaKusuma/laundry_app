<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <!-- <h1 class="h2"><?= $title; ?></h1> -->
    </div>

    <?php if (validation_errors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?= validation_errors(); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-user-tag me-2"></i> Form Pelanggan
                    </h5>
                </div>

                <div class="card-body p-4">

                    <?php
                    $is_edit = !empty($pelanggan->id);
                    $url_action = $is_edit ? base_url('pelanggan/update') : base_url('pelanggan/simpan');
                    ?>

                    <form action="<?= $url_action; ?>" method="post" id="form-pelanggan">

                        <input type="hidden" name="id" value="<?= $pelanggan->id ?? ''; ?>">

                        <div class="mb-3">
                            <label for="nama" class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                value="<?= $pelanggan->nama ?? ''; ?>"
                                placeholder="Nama Pelanggan" required>
                        </div>

                        <div class="mb-3">
                            <label for="no_hp" class="form-label fw-bold">No HP (WhatsApp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">+62</span>
                                <input type="number" class="form-control" id="no_hp" name="no_hp"
                                    value="<?= $pelanggan->no_hp ?? ''; ?>"
                                    placeholder="8123xxxx" required>
                            </div>
                            <small class="text-muted">Masukkan angka saja, tanpa 0 atau 62 di depan.</small>
                        </div>

                        <div class="mb-4">
                            <label for="alamat" class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"
                                placeholder="Alamat domisili..." required><?= $pelanggan->alamat ?? ''; ?></textarea>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('pelanggan'); ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-save me-2"></i> Simpan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#form-pelanggan').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Simpan',
                text: "Pastikan data pelanggan sudah benar",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>