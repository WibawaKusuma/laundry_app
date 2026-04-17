<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">&nbsp;</h1>
        <a href="<?= base_url('transaksi'); ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="flash-data-error" data-flashdata="<?= $this->session->flashdata('error'); ?>"></div>

    <form action="<?= base_url('transaksi/simpan'); ?>" method="post">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-user me-2"></i> Data Pelanggan & Paket</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 align-items-start">
                    <div class="col-md-6 col-xl-4">
                        <label class="form-label fw-bold">Pilih Pelanggan</label>
                        <select name="id_pelanggan" id="select_pelanggan" class="form-select" required>
                            <option value="">-- Ketik Nama Pelanggan --</option>
                            <?php foreach ($pelanggan as $p) : ?>
                                <option value="<?= $p->id; ?>"><?= $p->nama; ?> (<?= $p->no_hp; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text mt-2">
                            Belum ada? <a href="<?= base_url('pelanggan/tambah'); ?>" class="text-decoration-none">
                                <i class="fas fa-plus-circle"></i> Tambah Pelanggan Baru
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label fw-bold">Pilih Kategori Layanan</label>
                        <select id="filter_kategori" class="form-select">
                            <option value="">-- Semua Kategori --</option>
                            <?php foreach ($kategori as $kat) : ?>
                                <option value="<?= $kat->id_kategori; ?>"><?= $kat->nama_kategori; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label fw-bold">Pilih Tipe Laundry</label>
                        <select id="filter_tipe" class="form-select">
                            <option value="">-- Semua Tipe --</option>
                            <?php foreach ($tipe as $tp) : ?>
                                <option value="<?= $tp->id_tipe; ?>"><?= $tp->nama_tipe; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label fw-bold">Pilih Paket Laundry</label>
                        <select id="id_paket" class="form-select">
                            <option value="">-- Pilih Paket --</option>
                            <?php foreach ($paket as $pk) : ?>
                                <option value="<?= $pk->id_paket_laundry; ?>"
                                    data-kategori="<?= $pk->id_kat; ?>"
                                    data-tipe="<?= $pk->id_tp; ?>">
                                    <?= $pk->nama_tipe; ?> - <?= $pk->nama_paket; ?> - Rp <?= number_format($pk->harga, 0, ',', '.'); ?> / <?= strtoupper($pk->nama_satuan ?? '-'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" id="info-paket-kosong" style="display:none">Tidak ada paket untuk kombinasi kategori dan tipe ini.</small>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label fw-bold">Jumlah Bawaan (Qty)</label>
                        <input type="number" id="qty" class="form-control" value="" min="0.1" step="0.01" placeholder="Contoh: 1.5 atau 2">
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label fw-bold d-block">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-success w-100 py-2" id="btn-tambah-cart">
                            <i class="fas fa-cart-plus me-2"></i>Masukkan Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i> Keranjang Cucian</h6>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save me-2"></i> Simpan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Paket</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tabel-cart"></tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">GRAND TOTAL :</td>
                                <td class="text-end text-primary fw-bold">Rp <span id="total-bayar">0</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </form>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {

        loadCart();

        function loadCart() {
            $.ajax({
                url: '<?= base_url("transaksi/show_cart") ?>',
                type: 'GET',
                dataType: 'JSON',
                success: function(data) {
                    $('#tabel-cart').html(data.html);
                    $('#total-bayar').text(data.total_bayar);
                }
            });
        }

        $('#btn-tambah-cart').click(function() {
            var id_paket = $('#id_paket').val();
            var qty = $('#qty').val();

            if (id_paket == '' || qty == '') {
                Swal.fire('Ups!', 'Pilih paket dan tentukan jumlahnya dulu ya!', 'warning');
                return;
            }

            $.ajax({
                url: '<?= base_url("transaksi/add_to_cart") ?>',
                type: 'POST',
                data: {
                    id_paket: id_paket,
                    qty: qty
                },
                dataType: 'JSON',
                success: function(response) {
                    if (response.status == 'success') {
                        loadCart();
                        $('#id_paket').val('').trigger('change');
                        $('#qty').val('');
                        Swal.fire({
                            icon: 'success',
                            title: 'Masuk Keranjang',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                }
            });
        });

        $(document).on('click', '.btn-hapus-cart', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '<?= base_url("transaksi/hapus_cart") ?>',
                type: 'POST',
                data: {
                    id: id
                },
                success: function() {
                    loadCart();
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#select_pelanggan').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari nama atau nomor HP...',
            allowClear: true,
            width: '100%'
        });

        function initSelect2Paket() {
            $('#id_paket').select2({
                theme: 'bootstrap-5',
                placeholder: 'Ketik untuk mencari paket...',
                allowClear: true,
                width: '100%'
            });
        }
        initSelect2Paket();

        var semuaOpsiPaket = $('#id_paket option').not(':first').clone();

        function filterPaketOptions() {
            var selectedKat = $('#filter_kategori').val();
            var selectedTipe = $('#filter_tipe').val();

            $('#id_paket').select2('destroy');
            $('#id_paket').find('option:not(:first)').remove();
            $('#id_paket').val('');

            var filtered = semuaOpsiPaket.filter(function() {
                var cocokKategori = selectedKat === '' || $(this).data('kategori') == selectedKat;
                var cocokTipe = selectedTipe === '' || $(this).data('tipe') == selectedTipe;
                return cocokKategori && cocokTipe;
            });

            if (filtered.length > 0) {
                $('#id_paket').append(filtered.clone());
                $('#info-paket-kosong').hide();
            } else {
                $('#info-paket-kosong').show();
            }

            initSelect2Paket();
        }

        $('#filter_kategori').on('change', filterPaketOptions);
        $('#filter_tipe').on('change', filterPaketOptions);
    });
</script>