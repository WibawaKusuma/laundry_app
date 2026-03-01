</div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // A. Notifikasi Sukses/Gagal
        const flashSuccess = $('.flash-data-success').data('flashdata');
        if (flashSuccess) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: flashSuccess,
                timer: 3000,
                showConfirmButton: false
            });
        }

        const flashError = $('.flash-data-error').data('flashdata');
        if (flashError) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: flashError,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Perbaiki'
            });
        }

        const waLink = "<?= $this->session->flashdata('wa_link'); ?>";

        // Jika ada link WA (artinya baru saja simpan transaksi)
        if (waLink) {
            Swal.fire({
                title: 'Transaksi Berhasil!',
                text: "Ingin kirim struk via WhatsApp ke pelanggan?",
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#25D366', // Warna Hijau WA
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="fab fa-whatsapp"></i> Kirim WA',
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect langsung ke WA - kompatibel semua device
                    window.location.href = waLink;
                }
            });
        }

        // B. Konfirmasi Tombol Hapus
        $(document).on('click', '.btn-hapus', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');

            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Data ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.location.href = href;
                }
            });
        });
    });
</script>

<div id="sidebar-overlay" class="sidebar-overlay"></div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebarMenu = document.getElementById('sidebarMenu');
        const overlay = document.getElementById('sidebar-overlay');
        const togglerBtn = document.querySelector('.navbar-toggler');

        if (sidebarMenu && overlay) {

            // 1. SINKRONISASI: Ambil kontrol yang sudah ada, JANGAN buat baru (new)
            // Ini kunci agar tombol tidak macet!
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(sidebarMenu, {
                toggle: false
            });

            // 2. Event Listener: Munculkan/Hilangkan Blur
            sidebarMenu.addEventListener('show.bs.collapse', function() {
                overlay.classList.add('active');
            });
            sidebarMenu.addEventListener('hide.bs.collapse', function() {
                overlay.classList.remove('active');
            });

            // 3. Klik Blur untuk menutup
            overlay.addEventListener('click', function() {
                bsCollapse.hide();
            });

            // 4. Fix Tombol Navbar (Breadcrumb)
            // Memaksa tombol sinkron jika diklik
            if (togglerBtn) {
                togglerBtn.addEventListener('click', function(e) {
                    if (sidebarMenu.classList.contains('show')) {
                        bsCollapse.hide();
                    } else {
                        bsCollapse.show();
                    }
                });
            }
        }
    });
</script>

</body>

</html>