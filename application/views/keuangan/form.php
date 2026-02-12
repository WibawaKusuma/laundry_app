<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <!-- <h1 class="h2"><?= $title; ?></h1> -->
    </div>

    <?php
    $is_edit = isset($pengeluaran);
    $url_form = $is_edit ? base_url('keuangan/update') : base_url('keuangan/simpan');
    ?>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="<?= $is_edit ? 'fas fa-edit' : 'fas fa-plus-circle'; ?> me-2"></i>
                        <?= $is_edit ? 'Edit Pengeluaran' : 'Form Pengeluaran Baru'; ?>
                    </h6>
                </div>

                <div class="card-body p-4">
                    <form action="<?= $url_form; ?>" method="post" id="form-keuangan">

                        <?php if ($is_edit): ?>
                            <input type="hidden" name="id" value="<?= $pengeluaran->id; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Pengeluaran</label>
                            <input type="date" class="form-control" name="tgl_pengeluaran"
                                value="<?= $is_edit ? $pengeluaran->tgl_pengeluaran : date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Keterangan / Nama Barang</label>
                            <input type="text" class="form-control" name="keterangan"
                                value="<?= $is_edit ? $pengeluaran->keterangan : ''; ?>"
                                placeholder="Contoh: Beli Sabun, Bayar Listrik..." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nominal (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rp</span>
                                <input type="number" class="form-control" name="nominal"
                                    value="<?= $is_edit ? $pengeluaran->nominal : ''; ?>"
                                    placeholder="0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Catatan Tambahan (Opsional)</label>
                            <textarea class="form-control" name="catatan" rows="3"
                                placeholder="Keterangan detail..."><?= $is_edit ? $pengeluaran->catatan : ''; ?></textarea>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('keuangan'); ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-sm btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Simpan
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
        $('#form-keuangan').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Simpan',
                text: "Pastikan nominal dan data pengeluaran sudah benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>