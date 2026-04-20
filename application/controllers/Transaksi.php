<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi extends MY_Controller
{
    private $allowed_statuses = ['Baru', 'Proses', 'Selesai', 'Diambil'];
    private $allowed_payment_statuses = ['Belum Dibayar', 'Sudah Dibayar'];
    private $promo_config_key = 'promo';
    private $promo_free_kg = 3;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');

        if (empty($this->session->userdata('role'))) {
            redirect('auth/login');
        }
    }

    private function get_promo_settings()
    {
        $is_enabled = isset($this->company[$this->promo_config_key]) && (string) $this->company[$this->promo_config_key] === '1';

        return [
            'is_enabled' => $is_enabled,
            'config_key' => $this->promo_config_key,
            'label' => 'Promo Gratis Cuci 3 Kg',
            'free_qty' => $this->promo_free_kg,
        ];
    }

    private function is_kg_unit($unit_name)
    {
        $unit = strtolower(trim((string) $unit_name));
        return in_array($unit, ['kg', 'kgs', 'kilo', 'kilogram'], true);
    }

    private function sanitize_qty($qty)
    {
        $qty = (float) $qty;
        return $qty > 0 ? $qty : 0;
    }

    private function is_promo_service_eligible($nama_tipe, $nama_paket = '')
    {
        $service_name = strtolower(trim((string) $nama_tipe . ' ' . $nama_paket));
        $service_name = preg_replace('/\s+/', ' ', $service_name);

        if (strpos($service_name, 'setrika') !== false && strpos($service_name, 'cuci') === false) {
            return false;
        }

        return strpos($service_name, 'cuci komplit') !== false || strpos($service_name, 'cuci setrika') !== false;
    }

    private function calculate_cart_pricing($harga, $qty, $nama_satuan, $promo_requested = false, $nama_tipe = '', $nama_paket = '')
    {
        $settings = $this->get_promo_settings();
        $actual_qty = $this->sanitize_qty($qty);
        $promo_applied = $promo_requested
            && $settings['is_enabled']
            && $this->is_kg_unit($nama_satuan)
            && $this->is_promo_service_eligible($nama_tipe, $nama_paket)
            && $actual_qty > 0;

        $rounded_qty = $promo_applied ? (float) ceil($actual_qty) : $actual_qty;
        $charged_qty = $promo_applied ? max(0, $rounded_qty - $settings['free_qty']) : $actual_qty;
        $free_qty = $promo_applied ? min($settings['free_qty'], $rounded_qty) : 0;

        return [
            'actual_qty' => $actual_qty,
            'rounded_qty' => $rounded_qty,
            'charged_qty' => $charged_qty,
            'free_qty' => $free_qty,
            'subtotal' => (float) $harga * $charged_qty,
            'promo_requested' => (bool) $promo_requested,
            'promo_applied' => $promo_applied,
            'promo_label' => $settings['label'],
        ];
    }

    private function normalize_cart_item($item)
    {
        $pricing = $this->calculate_cart_pricing(
            $item['harga'] ?? 0,
            $item['qty'] ?? 0,
            $item['nama_satuan'] ?? '',
            !empty($item['promo_requested']),
            $item['nama_tipe'] ?? '',
            $item['nama_paket'] ?? ''
        );

        $item['qty'] = $pricing['actual_qty'];
        $item['rounded_qty'] = $pricing['rounded_qty'];
        $item['charged_qty'] = $pricing['charged_qty'];
        $item['promo_free_qty'] = $pricing['free_qty'];
        $item['promo_requested'] = $pricing['promo_requested'];
        $item['promo_applied'] = $pricing['promo_applied'];
        $item['promo_label'] = $pricing['promo_label'];
        $item['subtotal'] = $pricing['subtotal'];

        return $item;
    }

    private function get_normalized_cart($persist = true)
    {
        $cart = $this->session->userdata('cart');
        $normalized_cart = [];

        if (empty($cart) || !is_array($cart)) {
            return [];
        }

        foreach ($cart as $key => $item) {
            $normalized_cart[$key] = $this->normalize_cart_item($item);
        }

        if ($persist) {
            $this->session->set_userdata('cart', $normalized_cart);
        }

        return $normalized_cart;
    }

    private function build_promo_keterangan($item)
    {
        if (empty($item['promo_applied'])) {
            return '';
        }

        return json_encode([
            'promo_type' => 'free_3kg',
            'promo_label' => $item['promo_label'] ?? 'Promo Gratis Cuci 3 Kg',
            'actual_qty' => (float) ($item['qty'] ?? 0),
            'rounded_qty' => (float) ($item['rounded_qty'] ?? 0),
            'charged_qty' => (float) ($item['charged_qty'] ?? 0),
            'free_qty' => (float) ($item['promo_free_qty'] ?? 0),
            'unit' => strtolower((string) ($item['nama_satuan'] ?? 'kg')),
        ]);
    }

    private function parse_promo_keterangan($keterangan)
    {
        if (empty($keterangan)) {
            return null;
        }

        $decoded = json_decode($keterangan, true);
        if (!is_array($decoded) || empty($decoded['promo_type'])) {
            return null;
        }

        return $decoded;
    }

    private function enrich_detail_rows($details)
    {
        foreach ($details as $detail) {
            $promo = $this->parse_promo_keterangan($detail->keterangan ?? '');

            $detail->actual_qty = (float) $detail->qty;
            $detail->rounded_qty = (float) $detail->qty;
            $detail->charged_qty = (float) $detail->qty;
            $detail->promo_free_qty = 0;
            $detail->promo_applied = false;
            $detail->promo_label = '';
            $detail->qty_label = rtrim(rtrim(number_format((float) $detail->qty, 2, '.', ''), '0'), '.');
            $detail->subtotal = (float) $detail->harga * (float) $detail->qty;

            if ($promo) {
                $detail->actual_qty = (float) ($promo['actual_qty'] ?? $detail->qty);
                $detail->rounded_qty = (float) ($promo['rounded_qty'] ?? $detail->actual_qty);
                $detail->charged_qty = (float) ($promo['charged_qty'] ?? $detail->qty);
                $detail->promo_free_qty = (float) ($promo['free_qty'] ?? 0);
                $detail->promo_applied = true;
                $detail->promo_label = $promo['promo_label'] ?? 'Promo Gratis Cuci 3 Kg';
                $detail->qty_label = rtrim(rtrim(number_format($detail->actual_qty, 2, '.', ''), '0'), '.');
                $detail->subtotal = (float) $detail->harga * $detail->charged_qty;
            }
        }

        return $details;
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
        $data['promo_settings'] = $this->get_promo_settings();
        $data['pelanggan'] = $this->db->get('m_pelanggan')->result();
        $data['kategori'] = $this->db->get('m_kategori')->result();
        $data['tipe'] = $this->db->get('m_tipe')->result();

        $this->db->select('m_paket_laundry.*, m_satuan.nama_satuan, m_kategori.nama_kategori, m_kategori.id_kategori as id_kat, m_tipe.nama_tipe, m_tipe.id_tipe as id_tp');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
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
        $promo_requested = $this->input->post('promo_cuci_3kg') == '1';

        $this->db->select('m_paket_laundry.*, m_satuan.nama_satuan, m_kategori.nama_kategori, m_tipe.nama_tipe');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        $this->db->where('id_paket_laundry', $id_paket);
        $paket = $this->db->get()->row();

        if ($paket) {
            $pricing = $this->calculate_cart_pricing($paket->harga, $qty, $paket->nama_satuan, $promo_requested, $paket->nama_tipe, $paket->nama_paket);
            $item = [
                'id' => $paket->id_paket_laundry,
                'nama_paket' => $paket->nama_paket,
                'nama_satuan' => $paket->nama_satuan,
                'nama_kategori' => $paket->nama_kategori,
                'nama_tipe' => $paket->nama_tipe,
                'harga' => $paket->harga,
                'qty' => $pricing['actual_qty'],
                'rounded_qty' => $pricing['rounded_qty'],
                'charged_qty' => $pricing['charged_qty'],
                'promo_free_qty' => $pricing['free_qty'],
                'promo_requested' => $pricing['promo_requested'],
                'promo_applied' => $pricing['promo_applied'],
                'promo_label' => $pricing['promo_label'],
                'subtotal' => $pricing['subtotal']
            ];

            if (!$this->session->userdata('cart')) {
                $cart = [];
            } else {
                $cart = $this->session->userdata('cart');
            }

            $cart_key = $id_paket . '-' . ($pricing['promo_applied'] ? 'promo' : 'normal');

            if (isset($cart[$cart_key])) {
                $cart[$cart_key]['qty'] += $pricing['actual_qty'];
                $cart[$cart_key] = $this->normalize_cart_item($cart[$cart_key]);
            } else {
                $cart[$cart_key] = $item;
            }

            $this->session->set_userdata('cart', $cart);
            echo json_encode([
                'status' => 'success',
                'promo_applied' => $pricing['promo_applied']
            ]);
        }
    }

    public function show_cart()
    {
        $cart = $this->get_normalized_cart();
        $html = '';
        $total_bayar = 0;
        $no = 1;

        if (!empty($cart)) {
            foreach ($cart as $id => $item) {
                $total_bayar += $item['subtotal'];
                $qty_label = rtrim(rtrim(number_format((float) $item['qty'], 2, '.', ''), '0'), '.');
                $harga_label = 'Rp ' . number_format($item['harga'], 0, ',', '.');
                $promo_html = '';

                if (!empty($item['promo_applied'])) {
                    $promo_html = '<small class="d-block text-primary mt-1"><i class="fas fa-tags me-1"></i>' . $item['promo_label'] . ' aktif: berat asli ' . $qty_label . ' ' . $item['nama_satuan'] . ', dibulatkan ' . (float) $item['rounded_qty'] . ' ' . $item['nama_satuan'] . ', dibayar ' . (float) $item['charged_qty'] . ' ' . $item['nama_satuan'] . '.</small>';
                }

                $html .= '
                <tr>
                    <td>' . $no++ . '</td>
                    <td>' . $item['nama_paket'] . ' <small class="text-muted d-block">' . $item['nama_kategori'] . ' - ' . ($item['nama_tipe'] ?? '-') . '</small>' . $promo_html . '</td>
                    <td>' . $harga_label . '</td>
                    <td>' . $qty_label . ' ' . $item['nama_satuan'] . '</td>
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
        $cart = $this->get_normalized_cart();
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

            $total_tagihan += isset($item['subtotal']) ? (float) $item['subtotal'] : 0;
        }

        if ($max_jam == 0) {
            $max_jam = 24;
        }

        $tgl_selesai = date('Y-m-d H:i:s', strtotime("+$max_jam hours"));
        $invoice = 'INV-' . date('Ymd') . '-' . rand(100, 999);

        $data_transaksi = [
            'kode_invoice' => $invoice,
            'id_pelanggan' => $id_pelanggan,
            'tgl_masuk' => date('Y-m-d H:i:s'),
            'batas_waktu' => $tgl_selesai,
            'status' => $this->allowed_statuses[0],
            'dibayar' => $this->allowed_payment_statuses[0],
            'id_user' => $this->session->userdata('user_id')
        ];

        $this->db->insert('transaksi', $data_transaksi);
        $id_transaksi = $this->db->insert_id();

        $data_detail = [];
        $list_item_wa = "";

        foreach ($cart as $item) {
            $data_detail[] = [
                'id_transaksi' => $id_transaksi,
                'id_paket' => $item['id'],
                'qty' => $item['charged_qty'],
                'harga' => $item['harga'],
                'keterangan' => $this->build_promo_keterangan($item)
            ];

            $harga_satuan = isset($item['harga']) ? $item['harga'] : 0;
            $subtotal_item = isset($item['subtotal']) ? (float) $item['subtotal'] : 0;
            $satuan_wa = isset($item['nama_satuan']) ? $item['nama_satuan'] : 'Unit';
            $nama_tipe = isset($item['nama_tipe']) ? strtoupper($item['nama_tipe']) : 'LAYANAN';

            $list_item_wa .= '- ' . $nama_tipe . ' / ' . strtoupper($item['nama_paket']) . ', ' . (float) $item['qty'] . ' ' . strtoupper($satuan_wa) . "%0A";
            if (!empty($item['promo_applied'])) {
                $list_item_wa .= '  Promo 3 Kg gratis, dibayar ' . (float) $item['charged_qty'] . ' ' . strtoupper($satuan_wa) . "%0A";
            }
            $list_item_wa .= '@ Rp' . number_format($harga_satuan, 0, ',', '.') . ', Total Rp' . number_format($subtotal_item, 0, ',', '.') . "%0A";
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

            $company_name = $this->company['company_name'] ?? 'APP Laundry';
            $company_address = $this->company['company_address'] ?? 'Jalan';
            $company_phone = $this->company['company_phone'] ?? '08000000000';

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
        $data['status_options'] = [
            'Baru' => 'Baru Masuk',
            'Proses' => 'Sedang Dicuci',
            'Selesai' => 'Selesai (Siap Ambil)',
            'Diambil' => 'Sudah Diambil',
        ];

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
        $data['detail'] = $this->enrich_detail_rows($this->db->get()->result());

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

        if (!in_array($status_baru, $this->allowed_statuses, true)) {
            $this->session->set_flashdata('error', 'Status laundry tidak valid.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

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
            'status' => 'Diambil',
            'dibayar' => $this->allowed_payment_statuses[1],
            'tgl_bayar' => $tgl_bayar,
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
        $details = $this->enrich_detail_rows($this->db->get()->result());

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
                $nama_kasir = 'Admin';
            }

            $total_bayar = 0;
            $list_item_wa = '';
            foreach ($details as $d) {
                $subtotal = $d->subtotal;
                $total_bayar += $subtotal;
                $list_item_wa .= '- ' . strtoupper($d->nama_paket) . ', ' . $d->qty_label . " Kg/Pcs%0A";
                if (!empty($d->promo_applied)) {
                    $list_item_wa .= '  Promo 3 Kg gratis, dibayar ' . (float) $d->charged_qty . " Kg/Pcs%0A";
                }
            }
            $total_fmt = number_format($total_bayar, 0, ',', '.');

            $pesan = "FAKTUR BUKTI PENGAMBILAN%0A%0A";

            $company_name = $this->company['company_name'] ?? 'App Laundry';
            $company_address = $this->company['company_address'] ?? 'Jalan';
            $company_phone = $this->company['company_phone'] ?? '08000000000';

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
            $pesan .= "Diserahkan, $hari_ini, $tgl_jam%0A";
            $pesan .= "Oleh: $nama_kasir%0A%0A%0A";
            $pesan .= "Pembayaran:%0A";
            $metode = $this->db->get_where('m_metode_bayar', ['id' => $id_metode_bayar])->row();
            $nama_metode = $metode ? $metode->nama : 'Tunai';
            $pesan .= "$nama_metode Rp$total_fmt%0A%0A";
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
        $data['detail'] = $this->enrich_detail_rows($this->db->get()->result());

        $data['company'] = $this->company;
        $this->load->view('transaksi/cetak', $data);
    }
}
