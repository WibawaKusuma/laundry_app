<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <style>
        .member-point-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            border: 1px solid rgba(31, 41, 122, 0.14);
            background: #f7f9ff;
            color: #1f297a;
            font-weight: 700;
            line-height: 1;
        }

        .member-point-chip.member-point-chip--reward {
            border-color: #cfe7d6;
            background: #f4fbf6;
            color: #198754;
        }

        .pelanggan-pagination .btn {
            min-width: 34px;
        }

        .pelanggan-page-size {
            width: auto;
            min-width: 82px;
        }
    </style>

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
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <span>Tampilkan</span>
                            <select id="pelangganPageSize" class="form-select form-select-sm pelanggan-page-size">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>data</span>
                        </div>
                        <div id="pelangganPageInfo" class="text-muted small"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>No HP</th>
                                    <th>Poin Member</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabelPelanggan">
                                <?php if (empty($pelanggan)) : ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <p>Belum ada data pelanggan.</p>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php $no = 1;
                                    foreach ($pelanggan as $row) : ?>
                                        <?php $poin_member = (int) ($row->poin_member ?? 0); ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= $row->nama; ?></td>
                                            <td>
                                                <a href="https://wa.me/62<?= $row->no_hp; ?>" target="_blank" class="text-decoration-none">
                                                    <i class="fab fa-whatsapp text-success me-1"></i> <?= $row->no_hp; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($poin_member >= 8) : ?>
                                                    <div class="d-inline-flex align-items-center gap-2 flex-wrap">
                                                        <span class="member-point-chip member-point-chip--reward">
                                                            <span><?= $poin_member; ?></span>
                                                            <i class="fas fa-gift"></i>
                                                        </span>
                                                        <!-- <span class="badge rounded-pill text-bg-success">Reward</span> -->
                                                    </div>
                                                <?php else : ?>
                                                    <span class="member-point-chip">
                                                        <span><?= $poin_member; ?></span>
                                                        <i class="fas fa-coins"></i>
                                                    </span>
                                                <?php endif; ?>
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
                    <div class="d-flex justify-content-end mt-3">
                        <div id="pelangganPagination" class="btn-group btn-group-sm pelanggan-pagination" role="group" aria-label="Paginasi data pelanggan"></div>
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
        var pageSizeSelect = document.getElementById('pelangganPageSize');
        var pageInfo = document.getElementById('pelangganPageInfo');
        var pagination = document.getElementById('pelangganPagination');
        var currentRows = [];
        var currentPage = 1;
        var pageSize = parseInt(pageSizeSelect.value || '25', 10);

        function escapeHtml(value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getInitialRows() {
            var rows = [];
            tabelBody.querySelectorAll('tr').forEach(function(row) {
                if (row.children.length < 7 || row.querySelector('td[colspan]')) {
                    return;
                }

                rows.push({
                    no: row.children[0].textContent.trim(),
                    nama: row.children[1].textContent.trim(),
                    no_hp: row.children[2].textContent.trim(),
                    poin_member: row.children[3].textContent.trim().replace(/\D/g, '') || '0',
                    alamat: row.children[4].textContent.trim(),
                    aktif: row.children[5].textContent.indexOf('Non Aktif') > -1 ? '0' : '1',
                    id: (row.querySelector('a[href*="/pelanggan/edit/"]') || {}).href ? row.querySelector('a[href*="/pelanggan/edit/"]').href.split('/').pop() : ''
                });
            });
            return rows;
        }

        function renderRows(rows, page) {
            currentRows = rows || [];
            var totalRows = currentRows.length;
            var totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
            currentPage = Math.min(Math.max(parseInt(page || 1, 10), 1), totalPages);

            if (totalRows === 0) {
                tabelBody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">' +
                    '<p>Data pelanggan tidak ditemukan.</p></td></tr>';
                pageInfo.textContent = 'Tidak ada data';
                pagination.innerHTML = '';
                return;
            }

            var start = (currentPage - 1) * pageSize;
            var pageRows = currentRows.slice(start, start + pageSize);
            var html = '';

            for (var i = 0; i < pageRows.length; i++) {
                html += buildRowHtml(pageRows[i], start + i + 1);
            }

            tabelBody.innerHTML = html;
            pageInfo.textContent = 'Menampilkan ' + (start + 1) + '-' + (start + pageRows.length) + ' dari ' + totalRows + ' data';
            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            var pages = [];
            pages.push({ label: '&laquo;', page: Math.max(1, currentPage - 1), disabled: currentPage === 1 });

            var startPage = Math.max(1, currentPage - 2);
            var endPage = Math.min(totalPages, currentPage + 2);
            if (currentPage <= 3) {
                endPage = Math.min(totalPages, 5);
            }
            if (currentPage >= totalPages - 2) {
                startPage = Math.max(1, totalPages - 4);
            }

            for (var p = startPage; p <= endPage; p++) {
                pages.push({ label: p, page: p, active: p === currentPage });
            }

            pages.push({ label: '&raquo;', page: Math.min(totalPages, currentPage + 1), disabled: currentPage === totalPages });

            pagination.innerHTML = pages.map(function(item) {
                return '<button type="button" class="btn ' + (item.active ? 'btn-primary' : 'btn-outline-primary') + '" data-page="' + item.page + '"' + (item.disabled ? ' disabled' : '') + '>' + item.label + '</button>';
            }).join('');
        }

        function buildRowHtml(row, number) {
            var poinMember = parseInt(row.poin_member || 0, 10);
            var poinHtml = '';
            if (poinMember >= 8) {
                poinHtml =
                    '<div class="d-inline-flex align-items-center gap-2 flex-wrap">' +
                    '<span class="member-point-chip member-point-chip--reward">' +
                    '<span>' + poinMember + '</span>' +
                    '<i class="fas fa-gift"></i>' +
                    '</span>' +
                    '</div>';
            } else {
                poinHtml =
                    '<span class="member-point-chip">' +
                    '<span>' + poinMember + '</span>' +
                    '<i class="fas fa-coins"></i>' +
                    '</span>';
            }

            var statusBadge = parseInt(row.aktif || 0, 10) === 1 ?
                '<span class="badge bg-success">Aktif</span>' :
                '<span class="badge bg-secondary">Non Aktif</span>';
            var phone = escapeHtml(row.no_hp || '');
            var id = encodeURIComponent(row.id || '');

            return '<tr>' +
                '<td>' + number + '</td>' +
                '<td>' + escapeHtml(row.nama) + '</td>' +
                '<td><a href="https://wa.me/62' + phone + '" target="_blank" class="text-decoration-none">' +
                '<i class="fab fa-whatsapp text-success me-1"></i> ' + phone + '</a></td>' +
                '<td>' + poinHtml + '</td>' +
                '<td>' + escapeHtml(row.alamat) + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td class="text-center">' +
                '<a href="' + baseUrl + 'pelanggan/edit/' + id + '" class="btn btn-sm btn-outline-warning" title="Edit">' +
                '<i class="fas fa-edit"></i></a> ' +
                '<a href="' + baseUrl + 'pelanggan/hapus/' + id + '" class="btn btn-sm btn-outline-danger btn-hapus" title="Hapus">' +
                '<i class="fas fa-trash"></i></a>' +
                '</td>' +
                '</tr>';
        }

        currentRows = getInitialRows();
        renderRows(currentRows, 1);

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
                '<tr><td colspan="7" class="text-center py-4">' +
                '<i class="fas fa-spinner fa-spin me-2"></i>Mencari...</td></tr>';

            var xhr = new XMLHttpRequest();
            xhr.open('GET', baseUrl + 'pelanggan/search?keyword=' + encodeURIComponent(keyword), true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    renderRows(response, 1);
                } else {
                    tabelBody.innerHTML =
                        '<tr><td colspan="7" class="text-center py-5 text-danger">' +
                        '<p>Gagal memuat data. Silakan coba lagi.</p></td></tr>';
                }
            };

            xhr.onerror = function() {
                tabelBody.innerHTML =
                    '<tr><td colspan="7" class="text-center py-5 text-danger">' +
                    '<p>Gagal memuat data. Silakan coba lagi.</p></td></tr>';
            };

            xhr.send();
        }

        pageSizeSelect.addEventListener('change', function() {
            pageSize = parseInt(this.value || '25', 10);
            renderRows(currentRows, 1);
        });

        pagination.addEventListener('click', function(e) {
            var button = e.target.closest('button[data-page]');
            if (!button || button.disabled) {
                return;
            }

            renderRows(currentRows, parseInt(button.getAttribute('data-page'), 10));
        });
    });
</script>
