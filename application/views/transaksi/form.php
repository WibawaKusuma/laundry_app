<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">&nbsp;</h1>
        <a href="<?= base_url('transaksi'); ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="flash-data-error" data-flashdata="<?= $this->session->flashdata('error'); ?>"></div>

    <form action="<?= base_url('transaksi/simpan'); ?>" method="post">
        <div class="row">

            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i> Data Pelanggan & Paket</h6>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
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

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Paket Laundry</label>
                            <select id="id_paket" class="form-select">
                                <option value="">-- Pilih Paket --</option>
                                <?php foreach ($paket as $pk) : ?>
                                    <option value="<?= $pk->id; ?>">
                                        <?= $pk->nama_paket; ?> - Rp <?= number_format($pk->harga, 0, ',', '.'); ?> / <?= $pk->jenis; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Jumlah (Qty / Kg)</label>
                            <input type="number" id="qty" class="form-control" value="" min="0.1" step="0.01" placeholder="0.00">
                        </div>

                        <button type="button" class="btn btn-sm btn-success" id="btn-tambah-cart">
                            <i class="fas fa-cart-plus me-2"></i> Masukkan Keranjang
                        </button>

                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i> Keranjang Cucian</h6>
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
                                <tbody id="tabel-cart">
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-end">GRAND TOTAL :</td>
                                        <td class="text-end text-primary">Rp <span id="total-bayar">0</span></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white p-3 text-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-2"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {

        // Load Keranjang saat halaman dibuka pertama kali
        loadCart();

        // Fungsi Load Cart
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

        // Fungsi Tambah ke Cart
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
                        // Refresh Tabel
                        loadCart();
                        // Reset Input
                        $('#id_paket').val('');
                        $('#qty').val('');
                        // Notifikasi Kecil (Toast)
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

        // Fungsi Hapus Item Cart (Event Delegation)
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
        // Inisialisasi Select2 pada elemen dengan id="select_pelanggan"
        $('#select_pelanggan').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari nama atau nomor HP...',
            allowClear: true,
            width: '100%' // Memastikan lebar menyesuaikan container
        });
    });
</script>