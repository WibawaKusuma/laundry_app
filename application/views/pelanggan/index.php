<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="flash-data-success" data-flashdata="<?= $this->session->flashdata('success'); ?>"></div>

    <div class="flash-data-error" data-flashdata="<?= $this->session->flashdata('error'); ?>"></div>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-users me-2"></i> Data Pelanggan
                    </h5>
                    <a href="<?= base_url('pelanggan/tambah'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Baru
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>No HP</th>
                                    <th>Alamat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pelanggan)) : ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <p>Belum ada data pelanggan.</p>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php $no = 1;
                                    foreach ($pelanggan as $row) : ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class=""><?= $row->nama; ?></td>
                                            <td>
                                                <a href="https://wa.me/62<?= $row->no_hp; ?>" target="_blank" class="text-decoration-none">
                                                    <i class="fab fa-whatsapp text-success me-1"></i> <?= $row->no_hp; ?>
                                                </a>
                                            </td>
                                            <td><?= $row->alamat; ?></td>
                                            <td class="text-center">
                                                <a href="<?= base_url('pelanggan/edit/' . $row->id); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url('pelanggan/hapus/' . $row->id); ?>" class="btn btn-sm btn-outline-danger btn-hapus" title="Hapus">
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