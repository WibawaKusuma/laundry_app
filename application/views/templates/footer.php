</div>
</div>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>

</div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Pastikan DOM sudah siap
    $(document).ready(function() {

        // --- 1. LOGIKA FLASH DATA ---
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

        // --- 2. LOGIKA TOMBOL HAPUS (Revisi Lebih Kuat) ---
        // Kita pakai $(document).on agar bisa mendeteksi tombol 
        // meskipun tombol itu berada di dalam tabel yang kompleks
        $(document).on('click', '.btn-hapus', function(e) {
            e.preventDefault(); // Matikan aksi default link (PENTING!)

            const href = $(this).attr('href'); // Ambil link dari tombol

            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Data paket laundry ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Warna merah untuk tombol hapus
                cancelButtonColor: '#3085d6', // Warna biru untuk batal
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik Ya, baru kita arahkan ke link hapus
                    document.location.href = href;
                }
            });
        });

    }); // End Document Ready
</script>

</body>

</html>