<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load library yang dibutuhkan
        $this->load->library('form_validation');
    }

    // --- HALAMAN RIWAYAT TRANSAKSI (INDEX) ---
    public function index()
    {
        $this->db->select('transaksi.*, pelanggan.nama as nama_pelanggan');
        $this->db->from('transaksi');
        $this->db->join('pelanggan', 'pelanggan.id = transaksi.id_pelanggan');
        $this->db->order_by('transaksi.id', 'DESC');
        $data['transaksi'] = $this->db->get()->result();

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

    // --- PROSES SIMPAN TRANSAKSI KE DATABASE ---
    public function simpan()
    {
        $cart = $this->session->userdata('cart');
        $id_pelanggan = $this->input->post('id_pelanggan');

        if (empty($cart) || empty($id_pelanggan)) {
            $this->session->set_flashdata('error', 'Keranjang kosong atau Pelanggan belum dipilih!');
            redirect('transaksi/baru');
        }

        // 1. Generate Invoice (Format: INV-TGL-RANDOM)
        $invoice = 'INV-' . date('Ymd') . '-' . rand(100, 999);

        // 2. Data Header Transaksi
        $data_transaksi = [
            'kode_invoice' => $invoice,
            'id_pelanggan' => $id_pelanggan,
            'tgl_masuk'    => date('Y-m-d H:i:s'),
            'batas_waktu'  => date('Y-m-d H:i:s', strtotime('+3 days')), // Default 3 hari kerja
            'status'       => 'Baru',
            'dibayar'      => 'Belum Dibayar',
            'id_user'      => $this->session->userdata('id_user') // Ambil ID admin yg login
        ];

        // Simpan Header
        $this->db->insert('transaksi', $data_transaksi);
        $id_transaksi = $this->db->insert_id(); // Ambil ID Transaksi yang barusan dibuat

        // 3. Simpan Detail Paket (Looping Cart)
        $data_detail = [];
        foreach ($cart as $item) {
            $data_detail[] = [
                'id_transaksi' => $id_transaksi,
                'id_paket'     => $item['id'],
                'qty'          => $item['qty'],
                'keterangan'   => ''
            ];
        }
        $this->db->insert_batch('detail_transaksi', $data_detail);

        // 4. Bersihkan Cart & Redirect
        $this->session->unset_userdata('cart');
        $this->session->set_flashdata('success', 'Transaksi Berhasil Disimpan! Invoice: ' . $invoice);
        redirect('transaksi');
    }

    // --- FUNGSI BARU UNTUK TAHAP 3 (DETAIL & UPDATE) ---

    public function detail($kode_invoice)
    {
        $data['title'] = 'Detail Transaksi';

        // 1. Ambil Data Header Transaksi (Gabung dengan Pelanggan)
        $this->db->select('transaksi.*, pelanggan.nama as nama_pelanggan, pelanggan.no_hp');
        $this->db->from('transaksi');
        $this->db->join('pelanggan', 'pelanggan.id = transaksi.id_pelanggan');
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

    public function bayar_tagihan($kode_invoice)
    {
        // Update status bayar jadi lunas & set tanggal bayar sekarang
        $data = [
            'dibayar' => 'Sudah Dibayar',
            'tgl_bayar' => date('Y-m-d H:i:s')
        ];

        $this->db->where('kode_invoice', $kode_invoice);
        $this->db->update('transaksi', $data);

        $this->session->set_flashdata('success', 'Pembayaran Berhasil! Transaksi LUNAS.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    // --- FUNGSI CETAK INVOICE (STRUK) ---
    public function cetak($kode_invoice)
    {
        // 1. Ambil Data (Sama seperti fungsi detail)
        $this->db->select('transaksi.*, pelanggan.nama as nama_pelanggan, pelanggan.alamat');
        $this->db->from('transaksi');
        $this->db->join('pelanggan', 'pelanggan.id = transaksi.id_pelanggan');
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
        $this->load->view('transaksi/cetak', $data);
    }
}
