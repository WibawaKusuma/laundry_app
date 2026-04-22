<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="flash-data-success" data-flashdata="<?= $this->session->flashdata('success'); ?>"></div>
    <div class="flash-data-error" data-flashdata="<?= $this->session->flashdata('error'); ?>"></div>

    <div class="row">
        <div class="col-md-12">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="mb-0">
                            <i class="fas fa-box me-2"></i> Daftar Paket
                        </h5>
                        <a href="<?= base_url('paket/tambah'); ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Baru
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="<?= base_url('paket'); ?>" method="get" class="mb-4">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="q"
                                        value="<?= html_escape($keyword ?? ''); ?>"
                                        class="form-control border-start-0"
                                        placeholder="Cari paket, tipe, kategori, atau satuan...">
                                </div>
                            </div>
                            <div class="col-6 col-md-auto">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-1"></i> Cari
                                </button>
                            </div>
                            <?php if (!empty($keyword)) : ?>
                                <div class="col-6 col-md-auto">
                                    <a href="<?= base_url('paket'); ?>" class="btn btn-outline-secondary w-100">
                                        Reset
                                    </a>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Hasil pencarian untuk: <span class="fw-semibold text-dark"><?= html_escape($keyword); ?></span></small>
                                </div>
                            <?php else : ?>
                                <div class="col-12">
                                    <small class="text-muted">Gunakan pencarian untuk cepat menemukan paket yang ingin diperbaiki.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Kategori</th>
                                    <th>Tipe</th>
                                    <th>Paket</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>Durasi</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($paket)) : ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3"></i>
                                            <p class="mb-1">Tidak ada paket yang cocok.</p>
                                            <small><?= !empty($keyword) ? 'Coba gunakan kata kunci lain atau reset pencarian.' : 'Belum ada data paket laundry.'; ?></small>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php $no = 1;
                                    foreach ($paket as $row) : ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= $row->nama_kategori; ?></td>
                                            <td><?= $row->nama_tipe ?? '-'; ?></td>
                                            <td class="fw-bold"><?= $row->nama_paket; ?></td>
                                            <td><?= $row->nama_satuan; ?></td>
                                            <td>Rp <?= number_format($row->harga, 0, ',', '.'); ?></td>
                                            <td><?= $row->durasi_jam; ?> Jam</td>
                                            <td class="text-center">
                                                <a href="<?= base_url('paket/edit/' . $row->id_paket_laundry); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url('paket/hapus/' . $row->id_paket_laundry); ?>" class="btn btn-sm btn-outline-danger btn-hapus" title="Hapus">
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
