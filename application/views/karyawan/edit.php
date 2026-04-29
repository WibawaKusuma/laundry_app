<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <!-- <h1 class="h2">
            <?= isset($title) ? $title : 'Form Karyawan'; ?>
        </h1> -->
    </div>

    <?php
    $is_edit = isset($karyawan);
    $url_form = $is_edit ? base_url('karyawan/update') : base_url('karyawan/simpan');
    ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">

                <div class="card-header app-section-header py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i> Form Data Karyawan
                    </h5>
                </div>

                <div class="card-body">
                    <form action="<?= $url_form; ?>" method="post" id="form-karyawan">

                        <?php if ($is_edit): ?>
                            <input type="hidden" name="id" value="<?= $karyawan->id; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?= $is_edit ? $karyawan->name : ''; ?>"
                                placeholder="Masukkan nama lengkap..." required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label fw-bold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">@</span>
                                <input type="text" class="form-control" id="username" name="username"
                                    value="<?= $is_edit ? $karyawan->username : ''; ?>"
                                    placeholder="Buat username login" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Masukkan password...">

                            <?php if ($is_edit): ?>
                                <div class="form-text text-danger">
                                    <i class="fas fa-info-circle me-1"></i> Kosongkan jika tidak ingin mengubah password.
                                </div>
                            <?php else: ?>
                                <div class="form-text text-muted">
                                    Password wajib diisi untuk karyawan baru.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label fw-bold">Role / Jabatan</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">-- Pilih Role --</option>
                                <?php foreach ($role_options as $value => $label) : ?>
                                    <option value="<?= $value; ?>" <?= ($is_edit && $karyawan->role == $value) ? 'selected' : ''; ?>><?= $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('karyawan'); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>

        <div class="col-md-4">
            <div class="alert alert-info border-0 shadow-sm">
                <h6 class="alert-heading fw-bold"><i class="fas fa-info-circle me-2"></i>Info Role</h6>
                <p class="mb-0 small">
                    <strong>Admin:</strong> Memiliki akses penuh ke semua menu (Laporan, Keuangan, Manajemen User).
                </p>
                <hr>
                <p class="mb-0 small">
                    <strong>Kasir:</strong> Hanya memiliki akses ke menu Transaksi, Pelanggan, dan Dashboard.
                </p>
            </div>
        </div>

    </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Mencegat submit form dengan ID 'form-karyawan'
        $('#form-karyawan').on('submit', function(e) {
            e.preventDefault(); // Stop form biar tidak langsung kirim

            Swal.fire({
                title: 'Konfirmasi Simpan',
                text: "Apakah data karyawan sudah sesuai?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik Ya, baru kita submit form secara manual
                    this.submit();
                }
            });
        });
    });
</script>
