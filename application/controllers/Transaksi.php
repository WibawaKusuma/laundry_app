<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load library yang dibutuhkan
        $this->load->library('form_validation');

        if (empty($this->session->userdata('role'))) {
            redirect('auth/login');
        }
    }

    // --- HALAMAN RIWAYAT TRANSAKSI (INDEX) ---
    public function index()
    {
        // 1. Ambil data Tanggal dari URL (GET Request)
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        // 2. Validasi: Jika kosong (baru buka menu), set default 3 hari terakhir
        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal  = date('Y-m-d', strtotime('-2 days')); // 3 hari terakhir
            $tgl_akhir = date('Y-m-d');  // Hari ini
        }

        // 3. Query Database dengan Filter
        $this->db->select('transaksi.*, pelanggan.nama as nama_pelanggan');
        $this->db->from('transaksi');
        $this->db->join('pelanggan', 'pelanggan.id = transaksi.id_pelanggan');

        // --- FILTER DITERAPKAN DI SINI ---
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
        // ---------------------------------

        $this->db->order_by('transaksi.id', 'DESC');
        $data['transaksi'] = $this->db->get()->result();

        // 4. Kirim balik tanggal ke View (agar input tidak reset)
        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('transaksi/index', $data);
        $this->load->view('templates/footer');
    }

    // --- HALAMAN INPUT TRANSAKSI BARU ---
    public function baru()
    {
        $data['title'] = 'Input Transaksi Baru';

        // Ambil data untuk Dropdown
        $data['pelanggan'] = $this->db->get('pelanggan')->result();
        $data['paket'] = $this->db->get('paket_laundry')->result();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar'); // Sidebar Tetap Muncul
        $this->load->view('transaksi/form', $data);
        $this->load->view('templates/footer');
    }

    // --- LOGIKA KERANJANG BELANJA (AJAX) ---

    // 1. Tambah Item ke Session Cart
    public function add_to_cart()
    {
        $id_paket = $this->input->post('id_paket');
        $qty = $this->input->post('qty');

        // Ambil detail paket dari database
        $paket = $this->db->get_where('paket_laundry', ['id' => $id_paket])->row();

        if ($paket) {
            // Siapkan array item
            $item = [
                'id' => $paket->id,
                'nama_paket' => $paket->nama_paket,
                'harga' => $paket->harga,
                'qty' => $qty,
                'subtotal' => $paket->harga * $qty
            ];

            // Masukkan ke Session 'cart'
            // Jika cart belum ada, buat array baru
            if (!$this->session->userdata('cart')) {
                $cart = [];
            } else {
                $cart = $this->session->userdata('cart');
            }

            // Cek apakah paket sudah ada di cart? Kalau ada, update qty
            if (isset($cart[$id_paket])) {
                $cart[$id_paket]['qty'] += $qty;
                $cart[$id_paket]['subtotal'] = $cart[$id_paket]['harga'] * $cart[$id_paket]['qty'];
            } else {
                $cart[$id_paket] = $item;
            }

            $this->session->set_userdata('cart', $cart);
            echo json_encode(['status' => 'success']);
        }
    }

    // 2. Tampilkan Tabel Keranjang (Load HTML via AJAX)
    public function show_cart()
    {
        $cart = $this->session->userdata('cart');
        $html = '';
        $total_bayar = 0;
        $no = 1;

        if (!empty($cart)) {
            foreach ($cart as $id => $item) {
                $total_bayar += $item['subtotal'];
                $html .= '
                <tr>
                    <td>' . $no++ . '</td>
                    <td>' . $item['nama_paket'] . '</td>
                    <td>Rp ' . number_format($item['harga'], 0, ',', '.') . '</td>
                    <td>' . $item['qty'] . '</td>
                    <td class="text-end fw-bold">Rp ' . number_format($item['subtotal'], 0, ',', '.') . '</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-hapus-cart" data-id="' . $id . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                ';
            }
        } else {
            $html = '<tr><td colspan="6" class="text-center text-muted py-3">Keranjang Masih Kosong</td></tr>';
        }

        echo json_encode([
            'html' => $html,
            'total_bayar' => number_format($total_bayar, 0, ',', '.')
        ]);
    }

    // 3. Hapus Item Cart
    public function hapus_cart()
    {
        $id = $this->input->post('id');
        $cart = $this->session->userdata('cart');

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $this->session->set_userdata('cart', $cart);
        echo json_encode(['status' => 'success']);
    }

    // --- PROSES SIMPAN TRANSAKSI KE DATABASE (REVISI) ---
    public function simpan()
    {
        // 1. Ambil Data Session & Input
        $cart = $this->session->userdata('cart');
        $id_pelanggan = $this->input->post('id_pelanggan');

        if (empty($cart) || empty($id_pelanggan)) {
            $this->session->set_flashdata('error', 'Keranjang kosong atau Pelanggan belum dipilih!');
            redirect('transaksi/baru');
        }

        // 2. Hitung Estimasi Waktu & Total Biaya
        $max_jam = 0;
        $total_tagihan = 0;

        foreach ($cart as $item) {
            // Ambil data paket untuk cek durasi
            $paket_db = $this->db->get_where('paket_laundry', ['id' => $item['id']])->row();

            // Cek Durasi Terlama
            if ($paket_db && $paket_db->durasi_jam > $max_jam) {
                $max_jam = $paket_db->durasi_jam;
            }

            // Hitung Total Tagihan (Harga Paket dari Session x Qty)
            // Pastikan di session cart menyimpan 'harga' dan 'subtotal'
            // Jika di session tidak ada harga, ambil dari $paket_db->harga
            $harga_satuan = isset($item['harga']) ? $item['harga'] : ($paket_db->harga ?? 0);
            $total_tagihan += $harga_satuan * $item['qty'];
        }

        if ($max_jam == 0) $max_jam = 24;
        $tgl_selesai = date('Y-m-d H:i:s', strtotime("+$max_jam hours"));

        // 3. Simpan Header Transaksi
        $invoice = 'INV-' . date('Ymd') . '-' . rand(100, 999);

        $data_transaksi = [
            'kode_invoice' => $invoice,
            'id_pelanggan' => $id_pelanggan,
            'tgl_masuk'    => date('Y-m-d H:i:s'),
            'batas_waktu'  => $tgl_selesai,
            'status'       => 'Baru',
            'dibayar'      => 'Belum Dibayar',
            'id_user'      => $this->session->userdata('id_user')
        ];

        $this->db->insert('transaksi', $data_transaksi);
        $id_transaksi = $this->db->insert_id();

        // 4. Simpan Detail & Susun Pesan WA
        $data_detail = [];
        $list_item_wa = ""; // Variabel penampung list barang

        foreach ($cart as $item) {
            $data_detail[] = [
                'id_transaksi' => $id_transaksi,
                'id_paket'     => $item['id'],
                'qty'          => $item['qty'],
                'keterangan'   => ''
            ];

            // --- SUSUN FORMAT ITEM UNTUK WA ---
            // Format: ✅ [NAMA], [QTY] [SATUAN]
            //         @ [HARGA], Total [SUBTOTAL]

            // Cek harga (pastikan data harga ada)
            $harga_satuan = isset($item['harga']) ? $item['harga'] : 0;
            $subtotal_item = $harga_satuan * $item['qty'];

            $list_item_wa .= "✅ " . strtoupper($item['nama_paket']) . ", " . (float)$item['qty'] . " Kg/Pcs%0A";
            $list_item_wa .= "@ Rp" . number_format($harga_satuan, 0, ',', '.') . ", Total Rp" . number_format($subtotal_item, 0, ',', '.') . "%0A";
            $list_item_wa .= "Ket : -%0A"; // Bisa diganti jika ada input keterangan
        }

        $this->db->insert_batch('detail_transaksi', $data_detail);

        // 5. Generate Link WhatsApp (Sesuai Request)
        $pelanggan = $this->db->get_where('pelanggan', ['id' => $id_pelanggan])->row();
        $wa_link = "";

        if ($pelanggan && !empty($pelanggan->no_hp)) {
            // Ubah 08 jadi 628
            $nomor = trim($pelanggan->no_hp);
            if (substr($nomor, 0, 1) == '0') {
                $nomor = '62' . substr($nomor, 1);
            }

            // Format Tanggal
            $tgl_terima_fmt = date('d/m/Y H:i');
            $tgl_selesai_fmt = date('d/m/Y H:i', strtotime($tgl_selesai));
            $total_fmt = number_format($total_tagihan, 0, ',', '.');

            // --- SUSUN PESAN WA (TEKS POLOS) ---
            // Gunakan %0A untuk Enter (Baris Baru)

            $pesan = "FAKTUR ELEKTRONIK TRANSAKSI REGULER%0A";
            $pesan .= "APP Laundry%0A";
            $pesan .= "Jln. Sriwijaya no. 39,Br.Malkangin, Desa Dajan Peken, Tabanan%0A";
            $pesan .= "6287873894708%0A%0A";

            $pesan .= "Nomor Nota :%0A";
            $pesan .= "$invoice%0A%0A";

            $pesan .= "Pelanggan Yth :%0A";
            $pesan .= "$pelanggan->nama%0A%0A";

            $pesan .= "Terima : $tgl_terima_fmt%0A";
            $pesan .= "Selesai : $tgl_selesai_fmt%0A";

            $pesan .= "%0A======================%0A";
            $pesan .= "Detail pesanan:%0A";
            $pesan .= "Layanan:%0A";
            $pesan .= $list_item_wa; // Masukkan list item yg diloop tadi

            $pesan .= "%0A==============%0A";
            $pesan .= "Detail biaya :%0A";
            $pesan .= "Total tagihan : Rp$total_fmt%0A";
            $pesan .= "Grand total : Rp$total_fmt%0A%0A";

            $pesan .= "Pembayaran:%0A";
            $pesan .= "Sisa tagihan : Rp$total_fmt%0A";
            $pesan .= "Status: Belum lunas%0A%0A";

            $pesan .= "=================%0A";
            $pesan .= "Syarat dan ketentuan:%0A";
            $pesan .= "PERHATIAN :%0A";
            $pesan .= "1. Pengambilan barang harap disertai nota%0A";
            $pesan .= "2. Barang yang tidak diambil selama 1 bulan, hilang / rusak tidak diganti%0A";
            $pesan .= "3. Barang hilang/rusak karena proses pengerjaan diganti maksimal 5x biaya.%0A";
            $pesan .= "4. Klaim luntur tidak dipisah diluar tanggungan%0A";
            $pesan .= "5. Hak klaim berlaku 2 jam setelah barang diambil%0A";
            $pesan .= "6. Setiap konsumen dianggap setuju dengan isi perhitungan tersebut diatas%0A";

            $pesan .= "%0ATerima kasih";

            // Buat Link
            $wa_link = "https://wa.me/$nomor?text=$pesan";
        }

        // 6. Bersihkan Cart & Redirect
        $this->session->unset_userdata('cart');

        $this->session->set_flashdata('wa_link', $wa_link);
        $this->session->set_flashdata('success', 'Transaksi Berhasil Disimpan!');

        redirect('transaksi');
    }

    // --- FUNGSI BARU UNTUK TAHAP 3 (DETAIL & UPDATE) ---

    public function detail($kode_invoice)
    {
        $data['title'] = 'Detail Transaksi';

        // 1. Ambil Data Header Transaksi (Gabung dengan Pelanggan & Metode Bayar)
        $this->db->select('transaksi.*, pelanggan.nama as nama_pelanggan, pelanggan.no_hp, metode_bayar.nama as nama_metode_bayar');
        $this->db->from('transaksi');
        $this->db->join('pelanggan', 'pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('metode_bayar', 'metode_bayar.id = transaksi.id_metode_bayar', 'left');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $data['transaksi'] = $this->db->get()->row();

        // Jika data tidak ditemukan, balik ke index
        if (!$data['transaksi']) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan!');
            redirect('transaksi');
        }

        // 2. Ambil Data Detail Paket (Gabung dengan Paket)
        $this->db->select('detail_transaksi.*, paket_laundry.nama_paket, paket_laundry.harga');
        $this->db->from('detail_transaksi');
        $this->db->join('paket_laundry', 'paket_laundry.id = detail_transaksi.id_paket');
        $this->db->where('detail_transaksi.id_transaksi', $data['transaksi']->id);
        $data['detail'] = $this->db->get()->result();

        // 3. Ambil Daftar Metode Bayar (untuk dropdown)
        $this->db->where('is_active', 1);
        $data['metode_bayar'] = $this->db->get('metode_bayar')->result();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('transaksi/detail', $data);
        $this->load->view('templates/footer');
    }

    public function update_status()
    {
        $kode_invoice = $this->input->post('kode_invoice');
        $status_baru  = $this->input->post('status');

        $this->db->set('status', $status_baru);
        $this->db->where('kode_invoice', $kode_invoice);
        $this->db->update('transaksi');

        $this->session->set_flashdata('success', 'Status Laundry berhasil diupdate menjadi: ' . strtoupper($status_baru));
        redirect('transaksi/detail/' . $kode_invoice);
    }

    // --- PROSES BAYAR & AMBIL CUCIAN (UPDATE V2) ---
    public function bayar_tagihan($kode_invoice)
    {
        // 1. Ambil metode bayar dari POST
        $id_metode_bayar = $this->input->post('id_metode_bayar');

        // 2. Update Database (Status: Diambil & Lunas)
        $tgl_bayar = date('Y-m-d H:i:s');

        $data_update = [
            'status'         => 'Diambil',
            'dibayar'        => 'Sudah Dibayar',
            'tgl_bayar'      => $tgl_bayar,
            'id_metode_bayar' => $id_metode_bayar
        ];

        $this->db->where('kode_invoice', $kode_invoice);
        $this->db->update('transaksi', $data_update);

        // 3. Ambil Data Lengkap (Header & Detail) untuk Pesan WA
        // A. Header Transaksi & Pelanggan
        $this->db->select('transaksi.*, pelanggan.nama as nama_pelanggan, pelanggan.no_hp');
        $this->db->from('transaksi');
        $this->db->join('pelanggan', 'pelanggan.id = transaksi.id_pelanggan');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        // B. Detail Paket (Apa saja yang dicuci)
        $this->db->select('detail_transaksi.*, paket_laundry.nama_paket, paket_laundry.harga');
        $this->db->from('detail_transaksi');
        $this->db->join('paket_laundry', 'paket_laundry.id = detail_transaksi.id_paket');
        $this->db->where('detail_transaksi.id_transaksi', $trx->id);
        $details = $this->db->get()->result();

        // 4. Susun Pesan WA (Format Struk Pengambilan)
        $wa_link = "";

        if ($trx && !empty($trx->no_hp)) {
            // Ubah format nomor 08xx jadi 628xx
            $nomor = trim($trx->no_hp);
            if (substr($nomor, 0, 1) == '0') {
                $nomor = '62' . substr($nomor, 1);
            }

            // Nama Hari Indonesia
            $daftar_hari = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            $hari_ini = $daftar_hari[date('l')];
            $tgl_jam  = date('d/m/y H:i'); // Contoh: 26/12/25 18:50

            // Nama Kasir (User Login)
            $nama_kasir = $this->session->userdata('username');
            if (empty($nama_kasir)) $nama_kasir = "Admin";

            // Loop Detail Paket untuk Pesan
            $total_bayar = 0;
            $list_item_wa = "";
            foreach ($details as $d) {
                $subtotal = $d->harga * $d->qty;
                $total_bayar += $subtotal;

                // Format: ✅ PAKET REGULER, 2.8 KG
                $list_item_wa .= "✅ " . strtoupper($d->nama_paket) . ", " . (float)$d->qty . " Kg/Pcs%0A";
            }
            $total_fmt = number_format($total_bayar, 0, ',', '.');


            // --- SUSUN TEKS PESAN UTAMA ---
            $pesan = "FAKTUR BUKTI PENGAMBILAN%0A%0A";

            $pesan .= "App Laundry%0A";
            $pesan .= "Jln. Sriwijaya no. 39,Br.Malkangin, Desa Dajan Peken, Tabanan%0A";
            $pesan .= "6287873894708%0A%0A";

            $pesan .= "Nomor Nota :%0A";
            $pesan .= "$kode_invoice%0A%0A";

            $pesan .= "Pelanggan Yth :%0A";
            $pesan .= "$trx->nama_pelanggan%0A";

            $pesan .= "======================%0A";
            $pesan .= "DETAIL PENGAMBILAN:%0A%0A";

            $pesan .= $list_item_wa;

            $pesan .= "🕚 $hari_ini, $tgl_jam%0A";
            $pesan .= "🧔 $nama_kasir%0A%0A%0A";

            $pesan .= "Pembayaran:%0A";
            // Ambil nama metode bayar
            $metode = $this->db->get_where('metode_bayar', ['id' => $id_metode_bayar])->row();
            $nama_metode = $metode ? $metode->nama : 'Tunai';
            $pesan .= "💵 $nama_metode Rp$total_fmt%0A%0A";

            $pesan .= "Status: Lunas%0A";
            $pesan .= "=================%0A%0A";

            $pesan .= "Kami telah menyerahkan barang dan diterima dengan kondisi baik%0A";
            $pesan .= "Terima kasih";

            // Buat Link WA
            $wa_link = "https://wa.me/$nomor?text=$pesan";
        }

        // 5. Simpan Link WA ke Flashdata & Redirect
        $this->session->set_flashdata('wa_link', $wa_link);
        $this->session->set_flashdata('success', 'Pembayaran Berhasil! Cucian Diambil.');

        redirect('transaksi/detail/' . $kode_invoice);
    }

    // --- FUNGSI CETAK INVOICE (STRUK) ---
    public function cetak($kode_invoice)
    {
        // 1. Ambil Data (Sama seperti fungsi detail)
        $this->db->select('transaksi.*, pelanggan.nama as nama_pelanggan, pelanggan.alamat, metode_bayar.nama as nama_metode_bayar');
        $this->db->from('transaksi');
        $this->db->join('pelanggan', 'pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('metode_bayar', 'metode_bayar.id = transaksi.id_metode_bayar', 'left');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $data['transaksi'] = $this->db->get()->row();

        if (!$data['transaksi']) {
            redirect('transaksi');
        }

        // 2. Ambil Detail Barang
        $this->db->select('detail_transaksi.*, paket_laundry.nama_paket, paket_laundry.harga');
        $this->db->from('detail_transaksi');
        $this->db->join('paket_laundry', 'paket_laundry.id = detail_transaksi.id_paket');
        $this->db->where('detail_transaksi.id_transaksi', $data['transaksi']->id);
        $data['detail'] = $this->db->get()->result();

        // 3. Load View Khusus Cetak (Tanpa Header/Sidebar Admin)
        $data['company'] = $this->company;
        $this->load->view('transaksi/cetak', $data);
    }
}
