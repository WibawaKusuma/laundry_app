<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');

        if (empty($this->session->userdata('role'))) {
            redirect('auth/login');
        }
    }

    public function index()
    {
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal  = date('Y-m-d', strtotime('-2 days'));
            $tgl_akhir = date('Y-m-d');
        }

        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
        $this->db->order_by('transaksi.id', 'DESC');
        $data['transaksi'] = $this->db->get()->result();

        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('transaksi/index', $data);
        $this->load->view('templates/footer');
    }

    public function baru()
    {
        $data['title'] = 'Input Transaksi Baru';
        $data['pelanggan'] = $this->db->get('m_pelanggan')->result();
        $data['kategori'] = $this->db->get('m_kategori')->result();

        $this->db->select('m_paket_laundry.*, m_satuan.nama_satuan, m_kategori.nama_kategori, m_kategori.id_kategori as id_kat');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $data['paket'] = $this->db->get()->result();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('transaksi/form', $data);
        $this->load->view('templates/footer');
    }

    public function add_to_cart()
    {
        $id_paket = $this->input->post('id_paket');
        $qty = $this->input->post('qty');

        $this->db->select('m_paket_laundry.*, m_satuan.nama_satuan, m_kategori.nama_kategori');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->where('id_paket_laundry', $id_paket);
        $paket = $this->db->get()->row();

        if ($paket) {
            $item = [
                'id' => $paket->id_paket_laundry,
                'nama_paket' => $paket->nama_paket,
                'nama_satuan' => $paket->nama_satuan,
                'nama_kategori' => $paket->nama_kategori,
                'harga' => $paket->harga,
                'qty' => $qty,
                'subtotal' => $paket->harga * $qty
            ];

            if (!$this->session->userdata('cart')) {
                $cart = [];
            } else {
                $cart = $this->session->userdata('cart');
            }

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
                    <td>' . $item['nama_paket'] . ' <small class="text-muted d-block">' . $item['nama_kategori'] . '</small></td>
                    <td>Rp ' . number_format($item['harga'], 0, ',', '.') . '</td>
                    <td>' . $item['qty'] . ' ' . $item['nama_satuan'] . '</td>
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

    public function simpan()
    {
        $cart = $this->session->userdata('cart');
        $id_pelanggan = $this->input->post('id_pelanggan');

        if (empty($cart) || empty($id_pelanggan)) {
            $this->session->set_flashdata('error', 'Keranjang kosong atau Pelanggan belum dipilih!');
            redirect('transaksi/baru');
        }

        $max_jam = 0;
        $total_tagihan = 0;

        foreach ($cart as $item) {
            $paket_db = $this->db->get_where('m_paket_laundry', ['id_paket_laundry' => $item['id']])->row();

            if ($paket_db && $paket_db->durasi_jam > $max_jam) {
                $max_jam = $paket_db->durasi_jam;
            }

            $harga_satuan = isset($item['harga']) ? $item['harga'] : ($paket_db->harga ?? 0);
            $total_tagihan += $harga_satuan * $item['qty'];
        }

        if ($max_jam == 0) {
            $max_jam = 24;
        }

        $tgl_selesai = date('Y-m-d H:i:s', strtotime("+$max_jam hours"));
        $invoice = 'INV-' . date('Ymd') . '-' . rand(100, 999);

        $data_transaksi = [
            'kode_invoice' => $invoice,
            'id_pelanggan' => $id_pelanggan,
            'tgl_masuk'    => date('Y-m-d H:i:s'),
            'batas_waktu'  => $tgl_selesai,
            'status'       => 'Baru',
            'dibayar'      => 'Belum Dibayar',
            'id_user'      => $this->session->userdata('user_id')
        ];

        $this->db->insert('transaksi', $data_transaksi);
        $id_transaksi = $this->db->insert_id();

        $data_detail = [];
        $list_item_wa = "";

        foreach ($cart as $item) {
            $data_detail[] = [
                'id_transaksi' => $id_transaksi,
                'id_paket'     => $item['id'],
                'qty'          => $item['qty'],
                'harga'        => $item['harga'],
                'keterangan'   => ''
            ];

            $harga_satuan = isset($item['harga']) ? $item['harga'] : 0;
            $subtotal_item = $harga_satuan * $item['qty'];
            $satuan_wa = isset($item['nama_satuan']) ? $item['nama_satuan'] : 'Unit';

            $list_item_wa .= "âœ… " . strtoupper($item['nama_paket']) . ", " . (float) $item['qty'] . " " . strtoupper($satuan_wa) . "%0A";
            $list_item_wa .= "@ Rp" . number_format($harga_satuan, 0, ',', '.') . ", Total Rp" . number_format($subtotal_item, 0, ',', '.') . "%0A";
            $list_item_wa .= "Ket : -%0A";
        }

        $this->db->insert_batch('transaksi_detail', $data_detail);

        $pelanggan = $this->db->get_where('m_pelanggan', ['id' => $id_pelanggan])->row();
        $wa_link = "";

        if ($pelanggan && !empty($pelanggan->no_hp)) {
            $nomor = trim($pelanggan->no_hp);
            $nomor = str_replace([' ', '-', '+'], '', $nomor);
            if (substr($nomor, 0, 1) == '0') {
                $nomor = '62' . substr($nomor, 1);
            } elseif (substr($nomor, 0, 2) != '62') {
                $nomor = '62' . $nomor;
            }

            $tgl_terima_fmt = date('d/m/Y H:i');
            $tgl_selesai_fmt = date('d/m/Y H:i', strtotime($tgl_selesai));
            $total_fmt = number_format($total_tagihan, 0, ',', '.');

            $company_name    = $this->company['company_name'] ?? 'APP Laundry';
            $company_address = $this->company['company_address'] ?? 'Jalan';
            $company_phone   = $this->company['company_phone'] ?? '08000000000';

            $pesan = "FAKTUR ELEKTRONIK TRANSAKSI REGULER%0A";
            $pesan .= "{$company_name}%0A";
            $pesan .= "{$company_address}%0A";
            $pesan .= "{$company_phone}%0A%0A";
            $pesan .= "Nomor Nota :%0A";
            $pesan .= "$invoice%0A%0A";
            $pesan .= "Pelanggan Yth :%0A";
            $pesan .= "$pelanggan->nama%0A%0A";
            $pesan .= "Terima : $tgl_terima_fmt%0A";
            $pesan .= "Selesai : $tgl_selesai_fmt%0A";
            $pesan .= "%0A======================%0A";
            $pesan .= "Detail pesanan:%0A";
            $pesan .= "Layanan:%0A";
            $pesan .= $list_item_wa;
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

            $wa_link = "https://wa.me/$nomor?text=$pesan";
        }

        $this->session->unset_userdata('cart');
        $this->session->set_flashdata('wa_link', $wa_link);
        $this->session->set_flashdata('success', 'Transaksi Berhasil Disimpan!');

        redirect('transaksi');
    }

    public function detail($kode_invoice)
    {
        $data['title'] = 'Detail Transaksi';

        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.no_hp, m_metode_bayar.nama as nama_metode_bayar');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('m_metode_bayar', 'm_metode_bayar.id = transaksi.id_metode_bayar', 'left');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $data['transaksi'] = $this->db->get()->row();

        if (!$data['transaksi']) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan!');
            redirect('transaksi');
        }

        $this->db->select('transaksi_detail.*, m_paket_laundry.nama_paket, m_paket_laundry.harga');
        $this->db->from('transaksi_detail');
        $this->db->join('m_paket_laundry', 'm_paket_laundry.id_paket_laundry = transaksi_detail.id_paket');
        $this->db->where('transaksi_detail.id_transaksi', $data['transaksi']->id);
        $data['detail'] = $this->db->get()->result();

        $this->db->where('is_active', 1);
        $data['metode_bayar'] = $this->db->get('m_metode_bayar')->result();

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
        $id_metode_bayar = $this->input->post('id_metode_bayar');
        $tgl_bayar = date('Y-m-d H:i:s');

        $data_update = [
            'status'          => 'Diambil',
            'dibayar'         => 'Sudah Dibayar',
            'tgl_bayar'       => $tgl_bayar,
            'id_metode_bayar' => $id_metode_bayar
        ];

        $this->db->where('kode_invoice', $kode_invoice);
        $this->db->update('transaksi', $data_update);

        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.no_hp');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        $this->db->select('transaksi_detail.*, m_paket_laundry.nama_paket, m_paket_laundry.harga');
        $this->db->from('transaksi_detail');
        $this->db->join('m_paket_laundry', 'm_paket_laundry.id_paket_laundry = transaksi_detail.id_paket');
        $this->db->where('transaksi_detail.id_transaksi', $trx->id);
        $details = $this->db->get()->result();

        $wa_link = "";

        if ($trx && !empty($trx->no_hp)) {
            $nomor = trim($trx->no_hp);
            $nomor = str_replace([' ', '-', '+'], '', $nomor);
            if (substr($nomor, 0, 1) == '0') {
                $nomor = '62' . substr($nomor, 1);
            } elseif (substr($nomor, 0, 2) != '62') {
                $nomor = '62' . $nomor;
            }

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
            $tgl_jam  = date('d/m/y H:i');

            $nama_kasir = $this->session->userdata('username');
            if (empty($nama_kasir)) {
                $nama_kasir = "Admin";
            }

            $total_bayar = 0;
            $list_item_wa = "";
            foreach ($details as $d) {
                $subtotal = $d->harga * $d->qty;
                $total_bayar += $subtotal;
                $list_item_wa .= "âœ… " . strtoupper($d->nama_paket) . ", " . (float) $d->qty . " Kg/Pcs%0A";
            }
            $total_fmt = number_format($total_bayar, 0, ',', '.');

            $pesan = "FAKTUR BUKTI PENGAMBILAN%0A%0A";

            $company_name    = $this->company['company_name'] ?? 'App Laundry';
            $company_address = $this->company['company_address'] ?? 'Jalan';
            $company_phone   = $this->company['company_phone'] ?? '08000000000';

            $pesan .= "{$company_name}%0A";
            $pesan .= "{$company_address}%0A";
            $pesan .= "{$company_phone}%0A%0A";
            $pesan .= "Nomor Nota :%0A";
            $pesan .= "$kode_invoice%0A%0A";
            $pesan .= "Pelanggan Yth :%0A";
            $pesan .= "$trx->nama_pelanggan%0A";
            $pesan .= "======================%0A";
            $pesan .= "DETAIL PENGAMBILAN:%0A%0A";
            $pesan .= $list_item_wa;
            $pesan .= "ðŸ•š $hari_ini, $tgl_jam%0A";
            $pesan .= "ðŸ§” $nama_kasir%0A%0A%0A";
            $pesan .= "Pembayaran:%0A";
            $metode = $this->db->get_where('m_metode_bayar', ['id' => $id_metode_bayar])->row();
            $nama_metode = $metode ? $metode->nama : 'Tunai';
            $pesan .= "ðŸ’µ $nama_metode Rp$total_fmt%0A%0A";
            $pesan .= "Status: Lunas%0A";
            $pesan .= "=================%0A%0A";
            $pesan .= "Kami telah menyerahkan barang dan diterima dengan kondisi baik%0A";
            $pesan .= "Terima kasih";

            $wa_link = "https://wa.me/$nomor?text=$pesan";
        }

        $this->session->set_flashdata('wa_link', $wa_link);
        $this->session->set_flashdata('success', 'Pembayaran Berhasil! Cucian Diambil.');

        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function cetak($kode_invoice)
    {
        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.alamat, m_metode_bayar.nama as nama_metode_bayar');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('m_metode_bayar', 'm_metode_bayar.id = transaksi.id_metode_bayar', 'left');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $data['transaksi'] = $this->db->get()->row();

        if (!$data['transaksi']) {
            redirect('transaksi');
        }

        $this->db->select('transaksi_detail.*, m_paket_laundry.nama_paket, m_paket_laundry.harga');
        $this->db->from('transaksi_detail');
        $this->db->join('m_paket_laundry', 'm_paket_laundry.id_paket_laundry = transaksi_detail.id_paket');
        $this->db->where('transaksi_detail.id_transaksi', $data['transaksi']->id);
        $data['detail'] = $this->db->get()->result();

        $data['company'] = $this->company;
        $this->load->view('transaksi/cetak', $data);
    }
}
