<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="flash-data-success" data-flashdata="<?= $this->session->flashdata('success'); ?>"></div>

    <div class="flash-data-error" data-flashdata="<?= $this->session->flashdata('error'); ?>"></div>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header app-section-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i> Data Pelanggan
                    </h5>
                    <a href="<?= base_url('pelanggan/tambah'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Baru
                    </a>
                </div>
                <div class="card-body">
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="searchPelanggan" class="form-control border-start-0" placeholder="Cari nama atau no HP...">
                        <button class="btn btn-outline-secondary d-none" type="button" id="btnClearSearch" title="Hapus pencarian">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>No HP</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabelPelanggan">
                                <?php if (empty($pelanggan)) : ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <p>Belum ada data pelanggan.</p>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php $no = 1;
                                    foreach ($pelanggan as $row) : ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= $row->nama; ?></td>
                                            <td>
                                                <a href="https://wa.me/62<?= $row->no_hp; ?>" target="_blank" class="text-decoration-none">
                                                    <i class="fab fa-whatsapp text-success me-1"></i> <?= $row->no_hp; ?>
                                                </a>
                                            </td>
                                            <td><?= $row->alamat; ?></td>
                                            <td>
                                                <?php if ((int) ($row->aktif ?? 1) === 1) : ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else : ?>
                                                    <span class="badge bg-secondary">Non Aktif</span>
                                                <?php endif; ?>
                                            </td>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchTimer;
        var baseUrl = '<?= base_url(); ?>';
        var searchInput = document.getElementById('searchPelanggan');
        var btnClear = document.getElementById('btnClearSearch');
        var tabelBody = document.getElementById('tabelPelanggan');

        searchInput.addEventListener('keyup', function() {
            var keyword = this.value.trim();

            // Toggle clear button
            if (keyword.length > 0) {
                btnClear.classList.remove('d-none');
            } else {
                btnClear.classList.add('d-none');
            }

            clearTimeout(searchTimer);

            // Minimal 3 karakter untuk mulai search
            if (keyword.length > 0 && keyword.length < 3) {
                return;
            }

            // Jika kosong, load semua data
            if (keyword.length === 0) {
                loadPelanggan('');
                return;
            }

            // Debounce 300ms
            searchTimer = setTimeout(function() {
                loadPelanggan(keyword);
            }, 300);
        });

        btnClear.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            this.classList.add('d-none');
            loadPelanggan('');
        });

        function loadPelanggan(keyword) {
            tabelBody.innerHTML =
                '<tr><td colspan="6" class="text-center py-4">' +
                '<i class="fas fa-spinner fa-spin me-2"></i>Mencari...</td></tr>';

            var xhr = new XMLHttpRequest();
            xhr.open('GET', baseUrl + 'pelanggan/search?keyword=' + encodeURIComponent(keyword), true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    var html = '';
                    if (response.length === 0) {
                        html = '<tr><td colspan="6" class="text-center py-5 text-muted">' +
                            '<p>Data pelanggan tidak ditemukan.</p></td></tr>';
                    } else {
                        for (var i = 0; i < response.length; i++) {
                            var row = response[i];
                            var statusBadge = parseInt(row.aktif || 0, 10) === 1
                                ? '<span class="badge bg-success">Aktif</span>'
                                : '<span class="badge bg-secondary">Non Aktif</span>';
                            html += '<tr>';
                            html += '<td>' + (i + 1) + '</td>';
                            html += '<td>' + row.nama + '</td>';
                            html += '<td><a href="https://wa.me/62' + row.no_hp + '" target="_blank" class="text-decoration-none">';
                            html += '<i class="fab fa-whatsapp text-success me-1"></i> ' + row.no_hp + '</a></td>';
                            html += '<td>' + row.alamat + '</td>';
                            html += '<td>' + statusBadge + '</td>';
                            html += '<td class="text-center">';
                            html += '<a href="' + baseUrl + 'pelanggan/edit/' + row.id + '" class="btn btn-sm btn-outline-warning" title="Edit">';
                            html += '<i class="fas fa-edit"></i></a> ';
                            html += '<a href="' + baseUrl + 'pelanggan/hapus/' + row.id + '" class="btn btn-sm btn-outline-danger btn-hapus" title="Hapus">';
                            html += '<i class="fas fa-trash"></i></a>';
                            html += '</td>';
                            html += '</tr>';
                        }
                    }
                    tabelBody.innerHTML = html;
                } else {
                    tabelBody.innerHTML =
                        '<tr><td colspan="6" class="text-center py-5 text-danger">' +
                        '<p>Gagal memuat data. Silakan coba lagi.</p></td></tr>';
                }
            };

            xhr.onerror = function() {
                tabelBody.innerHTML =
                    '<tr><td colspan="6" class="text-center py-5 text-danger">' +
                    '<p>Gagal memuat data. Silakan coba lagi.</p></td></tr>';
            };

            xhr.send();
        }
    });
</script>
