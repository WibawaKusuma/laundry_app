<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="flash-data-success" data-flashdata="<?= $this->session->flashdata('success'); ?>"></div>

    <?php if ($this->session->flashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">

                <div class="card-header app-section-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users-cog me-2"></i> Daftar Pengguna
                    </h5>
                    <a href="<?= base_url('karyawan/tambah'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Baru
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Jabatan</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($karyawan)) : ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-user-slash fa-3x mb-3"></i>
                                            <p>Belum ada data karyawan.</p>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($karyawan as $index => $row) : ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td class=""><?= $row->name ?></td>
                                            <td><span class="text-muted">@</span><?= $row->username ?></td>
                                            <td>
                                                <?php if ($row->role == 'admin') : ?>
                                                    <span class="badge bg-primary rounded-pill">
                                                        <i class="fas fa-user-shield me-1"></i> ADMIN
                                                    </span>
                                                <?php else : ?>
                                                    <span class="badge bg-success rounded-pill">
                                                        <i class="fas fa-cash-register me-1"></i> KASIR
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= base_url('karyawan/edit/' . $row->id) ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url('karyawan/hapus/' . $row->id) ?>" class="btn btn-sm btn-outline-danger btn-hapus" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        // --- 1. NOTIFIKASI SUKSES (Muncul Otomatis setelah redirect) ---
        const flashData = $('.flash-data-success').data('flashdata');

        if (flashData) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: flashData,
                showConfirmButton: false,
                timer: 2000 // Popup hilang sendiri dalam 2 detik
            });
        }

        // --- 2. KONFIRMASI HAPUS (Saat tombol tong sampah diklik) ---
        $('.btn-hapus').on('click', function(e) {
            e.preventDefault(); // Matikan fungsi link asli
            const href = $(this).attr('href'); // Ambil link hapus

            Swal.fire({
                title: 'Yakin hapus data?',
                text: "Data karyawan akan hilang permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik Ya, baru diarahkan ke link hapus
                    document.location.href = href;
                }
            });
        });

    });
</script>