<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <?php
    $promo_enabled = !empty($promo_settings['is_enabled']);
    $member_settings = $member_settings ?? ['batas_poin' => 8, 'gratis_qty' => 3];
    $selected_pelanggan_id = (int) ($selected_pelanggan_id ?? 0);
    ?>

    <style>
        .member-info-box {
            padding: 0.9rem 1rem;
            border: 1px solid #cfe7d6;
            border-radius: 0.75rem;
            background: #f4fbf6;
            line-height: 1.55;
        }

        .trx-cart-card {
            border: 1px solid rgba(31, 41, 122, 0.1);
            border-radius: 1rem;
            background: #fff;
            overflow: hidden;
        }

        .trx-cart-card .card-body {
            padding-bottom: 0.75rem !important;
            background: #fff;
        }

        .trx-cart-card .table-responsive {
            background: #fff;
        }

        .trx-cart-card .table tfoot td {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">&nbsp;</h1>
        <a href="<?= base_url('transaksi'); ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="flash-data-error" data-flashdata="<?= $this->session->flashdata('error'); ?>"></div>

    <form action="<?= base_url('transaksi/simpan'); ?>" method="post">
        <div class="card shadow-sm border-0 mb-4 app-section-card">
            <div class="card-header app-section-header">
                <h6 class="mb-0"><i class="fas fa-user me-2 app-section-header-icon"></i> Data Pelanggan & Paket</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 align-items-start">
                    <div class="col-md-6 col-xl-4">
                        <label class="form-label fw-bold">Pilih Pelanggan</label>
                        <select name="id_pelanggan" id="select_pelanggan" class="form-select" required>
                            <option value="">-- Ketik Nama Pelanggan --</option>
                            <?php foreach ($pelanggan as $p) : ?>
                                <?php
                                $poin_member = (int) ($p->poin_member ?? 0);
                                $reward_tersedia = $poin_member >= (int) $member_settings['batas_poin'];
                                ?>
                                <option
                                    value="<?= $p->id; ?>"
                                    data-poin-member="<?= $poin_member; ?>"
                                    data-reward-tersedia="<?= $reward_tersedia ? '1' : '0'; ?>"
                                    <?= $selected_pelanggan_id === (int) $p->id ? 'selected' : ''; ?>>
                                    <?= $p->nama; ?> (<?= $p->no_hp; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text mt-2">
                            Belum ada? <a href="<?= base_url('pelanggan/tambah'); ?>" class="text-decoration-none">
                                <i class="fas fa-plus-circle"></i> Tambah Pelanggan Baru
                            </a>
                        </div>
                        <div id="info-lock-pelanggan" class="form-text text-primary mt-2 d-none">
                            Pelanggan dikunci selama keranjang masih berisi item.
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

                    <div class="col-12 col-xl-8">
                        <div class="row g-3 align-items-start">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Catatan Barang Bawaan</label>
                                <textarea id="customer_notes" class="form-control" rows="3" placeholder="Opsional. Contoh: celana 2 pcs, baju 3 pcs, sprei 1"></textarea>
                                <!-- <small class="text-muted">Catatan ini masih bisa diedit lagi selama transaksi masih status Baru.</small> -->
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Poin Member</label>
                                <div id="info-member-pelanggan" class="member-info-box small text-muted">
                                    Pilih pelanggan untuk melihat info poin member.
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($promo_enabled) : ?>
                        <div class="col-md-6 col-xl-4">
                            <label class="form-label fw-bold d-block">Promo Transaksi</label>
                            <div class="border rounded-3 p-3 bg-light h-100">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="promo_cuci_3kg">
                                    <label class="form-check-label fw-semibold" for="promo_cuci_3kg">
                                        Aktifkan <?= $promo_settings['label']; ?>
                                    </label>
                                </div>
                                <small class="text-muted d-block">
                                    Berlaku untuk layanan kiloan tipe Cuci Komplit dan Cuci Setrika. Berat dibulatkan ke atas lalu <?= $promo_settings['free_qty']; ?> kg pertama gratis.
                                </small>
                                <small class="text-primary d-block mt-1">
                                    Contoh: 3.8 kg dihitung 4 kg, yang dibayar hanya 1 kg. Layanan Setrika saja tidak mendapat promo.
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label fw-bold d-block">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-success w-100 py-2" id="btn-tambah-cart">
                            <i class="fas fa-cart-plus me-2"></i>Masukkan Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm trx-cart-card mb-4">
            <div class="card-header app-section-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Keranjang Cucian</h6>
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
        var formState = window.transaksiBaruForm = window.transaksiBaruForm || {
            lockedCustomerId: 0,
            renderMemberInfo: null
        };

        function loadCart() {
            $.ajax({
                url: '<?= base_url("transaksi/show_cart") ?>',
                type: 'GET',
                dataType: 'JSON',
                success: function(data) {
                    $('#tabel-cart').html(data.html);
                    $('#total-bayar').text(data.total_bayar);
                    syncLockedCustomer(data);
                }
            });
        }

        function renderMemberInfo() {
            var selectedOption = $('#select_pelanggan option:selected');
            var selectedId = $('#select_pelanggan').val();
            var infoEl = $('#info-member-pelanggan');
            var defaultHtml = 'Pilih pelanggan untuk melihat info poin member.';

            infoEl.removeClass('text-muted text-dark text-success border-success');

            if (!selectedId) {
                infoEl.addClass('text-muted').html(defaultHtml);
                return;
            }

            var poinRaw = parseInt(selectedOption.data('poin-member') || 0, 10);
            var rewardTersedia = String(selectedOption.data('reward-tersedia') || '0') === '1';
            var batasPoin = <?= (int) $member_settings['batas_poin']; ?>;
            var gratisQty = <?= (float) $member_settings['gratis_qty']; ?>;
            var poin = Math.min(Math.max(poinRaw, 0), batasPoin);
            var sisa = Math.max(0, batasPoin - poin);
            var html = '';

            if (rewardTersedia) {
                html = '<div class="fw-semibold mb-1"><i class="fas fa-gift me-2"></i>Poin member: ' + poin + '</div>' +
                    '<div>Customer ini berhak mendapat gratis cuci ' + gratisQty + ' kg untuk Cuci Komplit Reguler atau Satu Hari.</div>';
                infoEl.addClass('text-success border-success');
            } else {
                html = '<div class="fw-semibold mb-1"><i class="fas fa-coins me-2"></i>Poin member: ' + poin + '</div>' +
                    '<div>Butuh ' + sisa + ' transaksi Cuci Komplit lagi untuk mendapat gratis cuci ' + gratisQty + ' kg.</div>';
                infoEl.addClass('text-dark');
            }

            infoEl.html(html);
        }

        formState.renderMemberInfo = renderMemberInfo;

        function syncLockedCustomer(data) {
            formState.lockedCustomerId = parseInt(data.cart_customer_id || 0, 10);
            var hasItems = parseInt(data.item_count || 0, 10) > 0;

            if (formState.lockedCustomerId > 0 && $('#select_pelanggan').val() !== String(formState.lockedCustomerId)) {
                $('#select_pelanggan').val(String(formState.lockedCustomerId)).trigger('change');
            }

            $('#info-lock-pelanggan').toggleClass('d-none', !hasItems);
            renderMemberInfo();
        }

        loadCart();

        $('#btn-tambah-cart').click(function() {
            var id_paket = $('#id_paket').val();
            var qty = $('#qty').val();
            var id_pelanggan = $('#select_pelanggan').val();

            if (id_pelanggan == '') {
                Swal.fire('Ups!', 'Pilih pelanggan terlebih dahulu ya.', 'warning');
                return;
            }

            if (id_paket == '' || qty == '') {
                Swal.fire('Ups!', 'Pilih paket dan tentukan jumlahnya dulu ya!', 'warning');
                return;
            }

            $.ajax({
                url: '<?= base_url("transaksi/add_to_cart") ?>',
                type: 'POST',
                data: {
                    id_pelanggan: id_pelanggan,
                    id_paket: id_paket,
                    qty: qty,
                    promo_cuci_3kg: $('#promo_cuci_3kg').is(':checked') ? 1 : 0,
                    customer_notes: $('#customer_notes').val()
                },
                dataType: 'JSON',
                success: function(response) {
                    if (response.status == 'success') {
                        loadCart();
                        $('#id_paket').val('').trigger('change');
                        $('#qty').val('');
                        $('#customer_notes').val('');
                        $('#promo_cuci_3kg').prop('checked', false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Masuk Keranjang',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else if (response.message) {
                        Swal.fire('Ups!', response.message, 'warning');
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
        var formState = window.transaksiBaruForm = window.transaksiBaruForm || {
            lockedCustomerId: 0,
            renderMemberInfo: null
        };

        $('#select_pelanggan').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari nama atau nomor HP...',
            allowClear: true,
            width: '100%'
        });

        function handlePelangganChange() {
            if (formState.lockedCustomerId > 0 && $('#select_pelanggan').val() !== String(formState.lockedCustomerId)) {
                $('#select_pelanggan').val(String(formState.lockedCustomerId)).trigger('change.select2');
                Swal.fire('Keranjang aktif', 'Pelanggan tidak bisa diganti selama keranjang masih berisi item.', 'warning');
                return;
            }

            if (typeof formState.renderMemberInfo === 'function') {
                formState.renderMemberInfo();
            }
        }

        $('#select_pelanggan').on('change select2:select select2:clear', handlePelangganChange);

        if (typeof formState.renderMemberInfo === 'function') {
            formState.renderMemberInfo();
        }

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
            var selectedPaket = $('#id_paket').val();

            $('#id_paket').select2('destroy');
            $('#id_paket').find('option:not(:first)').remove();

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

            if (selectedPaket && $('#id_paket option[value="' + selectedPaket + '"]').length > 0) {
                $('#id_paket').val(selectedPaket).trigger('change.select2');
            } else {
                $('#id_paket').val('').trigger('change.select2');
            }
        }

        $('#filter_kategori').on('change', filterPaketOptions);
        $('#filter_tipe').on('change', filterPaketOptions);
        filterPaketOptions();
    });
</script>