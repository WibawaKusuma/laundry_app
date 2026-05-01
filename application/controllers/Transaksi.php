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
        $item['customer_notes'] = trim((string) ($item['customer_notes'] ?? ''));

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
        $catatan = trim((string) ($item['customer_notes'] ?? ''));

        if (empty($item['promo_applied']) && $catatan === '') {
            return '';
        }

        return json_encode([
            'promo_type' => !empty($item['promo_applied']) ? 'free_3kg' : null,
            'promo_label' => !empty($item['promo_applied']) ? ($item['promo_label'] ?? 'Promo Gratis Cuci 3 Kg') : null,
            'actual_qty' => !empty($item['promo_applied']) ? (float) ($item['qty'] ?? 0) : null,
            'rounded_qty' => !empty($item['promo_applied']) ? (float) ($item['rounded_qty'] ?? 0) : null,
            'charged_qty' => !empty($item['promo_applied']) ? (float) ($item['charged_qty'] ?? 0) : null,
            'free_qty' => !empty($item['promo_applied']) ? (float) ($item['promo_free_qty'] ?? 0) : null,
            'unit' => !empty($item['promo_applied']) ? strtolower((string) ($item['nama_satuan'] ?? 'kg')) : null,
            'customer_notes' => $catatan !== '' ? $catatan : null,
        ]);
    }

    private function parse_promo_keterangan($keterangan)
    {
        if (empty($keterangan)) {
            return null;
        }

        $decoded = json_decode($keterangan, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function extract_customer_notes($keterangan)
    {
        $decoded = $this->parse_promo_keterangan($keterangan);

        if (is_array($decoded) && isset($decoded['customer_notes'])) {
            return trim((string) $decoded['customer_notes']);
        }

        return '';
    }

    private function merge_keterangan_notes($keterangan, $notes)
    {
        $notes = trim((string) $notes);
        $decoded = $this->parse_promo_keterangan($keterangan);

        if (!is_array($decoded)) {
            return $notes;
        }

        $decoded['customer_notes'] = $notes !== '' ? $notes : null;

        $has_promo = !empty($decoded['promo_type']);
        $has_notes = !empty($decoded['customer_notes']);

        if (!$has_promo && !$has_notes) {
            return '';
        }

        return json_encode($decoded);
    }

    private function build_item_note_text($promo_applied, $charged_qty, $unit_name, $customer_notes = '')
    {
        $notes = [];

        if ($promo_applied) {
            $notes[] = 'Promo 3 Kg gratis, dibayar ' . (float) $charged_qty . ' ' . strtoupper((string) $unit_name);
        }

        $customer_notes = trim((string) $customer_notes);
        if ($customer_notes !== '') {
            $notes[] = $customer_notes;
        }

        if (empty($notes)) {
            return '-';
        }

        return implode(' | ', $notes);
    }

    private function get_package_with_meta($id_paket)
    {
        $this->db->select('m_paket_laundry.*, m_satuan.nama_satuan, m_kategori.nama_kategori, m_tipe.nama_tipe');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        $this->db->where('id_paket_laundry', (int) $id_paket);

        return $this->db->get()->row();
    }

    private function build_transaction_detail_item($paket, $qty, $promo_requested, $customer_notes = '')
    {
        $pricing = $this->calculate_cart_pricing(
            $paket->harga,
            $qty,
            $paket->nama_satuan,
            $promo_requested,
            $paket->nama_tipe,
            $paket->nama_paket
        );

        return [
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
            'customer_notes' => trim((string) $customer_notes),
            'subtotal' => $pricing['subtotal']
        ];
    }

    private function get_transaction_details($transaksi_id, $include_cancelled = false)
    {
        $this->db->select('
            transaksi_detail.id,
            transaksi_detail.id_transaksi,
            transaksi_detail.id_paket,
            transaksi_detail.qty,
            transaksi_detail.harga,
            transaksi_detail.keterangan,
            transaksi_detail.batal,
            transaksi_detail.batal_at,
            transaksi_detail.batal_by,
            transaksi_detail.alasan_batal,
            m_paket_laundry.nama_paket,
            m_paket_laundry.harga AS harga_master,
            m_tipe.nama_tipe,
            m_satuan.nama_satuan
        ');
        $this->db->from('transaksi_detail');
        $this->db->join('m_paket_laundry', 'm_paket_laundry.id_paket_laundry = transaksi_detail.id_paket');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->where('transaksi_detail.id_transaksi', $transaksi_id);

        if (!$include_cancelled) {
            $this->db->where('COALESCE(transaksi_detail.batal, 0) = 0', null, false);
        }

        return $this->enrich_detail_rows($this->db->get()->result());
    }

    private function get_active_transaction_details($transaksi_id)
    {
        return array_values(array_filter(
            $this->get_transaction_details($transaksi_id, true),
            static function ($detail) {
                return empty($detail->batal);
            }
        ));
    }

    private function count_active_transaction_details($transaksi_id)
    {
        $this->db->from('transaksi_detail');
        $this->db->where('id_transaksi', $transaksi_id);
        $this->db->where('COALESCE(batal, 0) = 0', null, false);

        return (int) $this->db->count_all_results();
    }

    private function can_modify_transaction_items($transaksi)
    {
        return $this->can_add_items_to_transaction($transaksi);
    }

    private function generate_unique_invoice_code()
    {
        $attempt = 0;

        do {
            $attempt++;
            $invoice = 'INV-' . date('Ymd') . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = $this->db
                ->where('kode_invoice', $invoice)
                ->count_all_results('transaksi') > 0;
        } while ($exists && $attempt < 20);

        if ($exists) {
            throw new RuntimeException('Gagal membuat nomor invoice unik.');
        }

        return $invoice;
    }

    private function normalize_whatsapp_number($phone_number)
    {
        $nomor = trim((string) $phone_number);
        $nomor = str_replace([' ', '-', '+'], '', $nomor);

        if ($nomor === '') {
            return '';
        }

        if (substr($nomor, 0, 1) === '0') {
            return '62' . substr($nomor, 1);
        }

        if (substr($nomor, 0, 2) !== '62') {
            return '62' . $nomor;
        }

        return $nomor;
    }

    private function build_confirmation_message($invoice, $pelanggan_nama, $tgl_terima, $tgl_selesai, $details, $total_tagihan)
    {
        $list_item_wa = '';

        foreach ($details as $item) {
            $nama_tipe = strtoupper(trim((string) ($item->nama_tipe ?? $item['nama_tipe'] ?? '')));
            $nama_paket = trim((string) ($item->nama_paket ?? $item['nama_paket'] ?? ''));
            $harga_satuan = (float) ($item->harga ?? $item['harga'] ?? 0);
            $satuan_wa = trim((string) ($item->nama_satuan ?? $item['nama_satuan'] ?? ''));
            $item_note_text = trim((string) ($item->item_note_text ?? $item['item_note_text'] ?? '-'));
            $qty_for_message = $item->qty_label ?? $item['qty_label'] ?? $item->qty ?? $item['qty'] ?? 0;
            $subtotal_item = (float) ($item->subtotal ?? $item['subtotal'] ?? ($harga_satuan * $qty_for_message));

            $list_item_wa .= '- ' . $nama_tipe . ' / ' . strtoupper($nama_paket) . ', ' . $qty_for_message . ' ' . strtoupper($satuan_wa) . "%0A";
            $list_item_wa .= '@ Rp' . number_format($harga_satuan, 0, ',', '.') . ', Total Rp' . number_format($subtotal_item, 0, ',', '.') . "%0A";
            $list_item_wa .= 'Ket : ' . rawurlencode($item_note_text) . "%0A";
        }

        $tgl_terima_fmt = date('d/m/Y H:i', strtotime($tgl_terima));
        $tgl_selesai_fmt = date('d/m/Y H:i', strtotime($tgl_selesai));
        $total_fmt = number_format((float) $total_tagihan, 0, ',', '.');

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
        $pesan .= "$pelanggan_nama%0A%0A";
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

        return $pesan;
    }

    private function build_confirmation_wa_link($phone_number, $invoice, $pelanggan_nama, $tgl_terima, $tgl_selesai, $details, $total_tagihan)
    {
        $nomor = $this->normalize_whatsapp_number($phone_number);

        if ($nomor === '') {
            return '';
        }

        $pesan = $this->build_confirmation_message($invoice, $pelanggan_nama, $tgl_terima, $tgl_selesai, $details, $total_tagihan);

        return "https://wa.me/$nomor?text=$pesan";
    }

    private function load_package_form_data()
    {
        $data = [];
        $data['promo_settings'] = $this->get_promo_settings();
        $data['kategori'] = $this->db->get('m_kategori')->result();
        $data['tipe'] = $this->db->get('m_tipe')->result();

        $this->db->select('m_paket_laundry.*, m_satuan.nama_satuan, m_kategori.nama_kategori, m_kategori.id_kategori as id_kat, m_tipe.nama_tipe, m_tipe.id_tipe as id_tp');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        $data['paket'] = $this->db->get()->result();

        return $data;
    }

    private function can_add_items_to_transaction($transaksi)
    {
        if (!$transaksi) {
            return false;
        }

        return (string) $transaksi->status === 'Baru'
            && (string) $transaksi->dibayar === 'Belum Dibayar';
    }

    private function get_add_item_block_reason($transaksi)
    {
        if (!$transaksi) {
            return 'Transaksi tidak ditemukan.';
        }

        if ((string) $transaksi->dibayar !== 'Belum Dibayar') {
            return 'Item baru hanya bisa ditambahkan jika transaksi belum dibayar.';
        }

        if ((string) $transaksi->status !== 'Baru') {
            return 'Item baru hanya bisa ditambahkan saat status transaksi masih Baru, sebelum laundry mulai diproses.';
        }

        return 'Transaksi ini sudah tidak bisa ditambahkan item.';
    }

    private function sync_transaction_deadline($transaksi_id)
    {
        $this->db->select('transaksi.id, transaksi.tgl_masuk');
        $this->db->from('transaksi');
        $this->db->where('transaksi.id', $transaksi_id);
        $trx = $this->db->get()->row();

        if (!$trx) {
            return;
        }

        $this->db->select_max('m_paket_laundry.durasi_jam', 'max_jam');
        $this->db->from('transaksi_detail');
        $this->db->join('m_paket_laundry', 'm_paket_laundry.id_paket_laundry = transaksi_detail.id_paket');
        $this->db->where('transaksi_detail.id_transaksi', $transaksi_id);
        $this->db->where('COALESCE(transaksi_detail.batal, 0) = 0', null, false);
        $result = $this->db->get()->row();

        $max_jam = (int) ($result->max_jam ?? 0);
        if ($max_jam <= 0) {
            $max_jam = 24;
        }

        $batas_waktu = date('Y-m-d H:i:s', strtotime($trx->tgl_masuk . " +$max_jam hours"));

        $this->db->where('id', $transaksi_id);
        $this->db->update('transaksi', ['batas_waktu' => $batas_waktu]);
    }

    private function enrich_detail_rows($details)
    {
        foreach ($details as $detail) {
            $promo = $this->parse_promo_keterangan($detail->keterangan ?? '');
            $detail->batal = !empty($detail->batal);

            $detail->actual_qty = (float) $detail->qty;
            $detail->rounded_qty = (float) $detail->qty;
            $detail->charged_qty = (float) $detail->qty;
            $detail->promo_free_qty = 0;
            $detail->promo_applied = false;
            $detail->promo_label = '';
            $detail->qty_label = rtrim(rtrim(number_format((float) $detail->qty, 2, '.', ''), '0'), '.');
            $detail->subtotal = (float) $detail->harga * (float) $detail->qty;
            $detail->customer_notes = $this->extract_customer_notes($detail->keterangan ?? '');
            $detail->item_note_text = '-';

            if ($promo) {
                $detail->actual_qty = (float) ($promo['actual_qty'] ?? $detail->qty);
                $detail->rounded_qty = (float) ($promo['rounded_qty'] ?? $detail->actual_qty);
                $detail->charged_qty = (float) ($promo['charged_qty'] ?? $detail->qty);
                $detail->promo_free_qty = (float) ($promo['free_qty'] ?? 0);
                $detail->promo_applied = !empty($promo['promo_type']);
                $detail->promo_label = $promo['promo_label'] ?? 'Promo Gratis Cuci 3 Kg';
                $detail->qty_label = rtrim(rtrim(number_format($detail->actual_qty, 2, '.', ''), '0'), '.');
                $detail->subtotal = (float) $detail->harga * $detail->charged_qty;
                $detail->customer_notes = trim((string) ($promo['customer_notes'] ?? $detail->customer_notes));
            }

            $detail->item_note_text = $this->build_item_note_text(
                !empty($detail->promo_applied),
                $detail->charged_qty,
                'Kg',
                $detail->customer_notes
            );
        }

        return $details;
    }

    public function index()
    {
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal  = date('Y-m-01');
            $tgl_akhir = date('Y-m-d');
        }

        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
        $this->db->order_by('transaksi.id', 'DESC');
        $data['transaksi'] = $this->db->get()->result();

        $ringkasan = [
            'total' => count($data['transaksi']),
            'belum_lunas' => 0,
            'siap_diambil' => 0,
            'lunas_belum_diambil' => 0,
            'terlambat' => 0,
        ];

        $now = time();
        foreach ($data['transaksi'] as $trx) {
            if ((string) $trx->dibayar !== 'Sudah Dibayar') {
                $ringkasan['belum_lunas']++;
            }

            if ((string) $trx->status === 'Selesai') {
                $ringkasan['siap_diambil']++;
            }

            if ((string) $trx->status === 'Selesai' && (string) $trx->dibayar === 'Sudah Dibayar') {
                $ringkasan['lunas_belum_diambil']++;
            }

            if ((string) $trx->status !== 'Diambil' && !empty($trx->batas_waktu) && strtotime($trx->batas_waktu) < $now) {
                $ringkasan['terlambat']++;
            }
        }

        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;
        $data['ringkasan'] = $ringkasan;

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('transaksi/index', $data);
        $this->load->view('templates/footer');
    }

    public function baru()
    {
        $data['title'] = 'Input Transaksi Baru';
        $package_data = $this->load_package_form_data();
        $data['promo_settings'] = $package_data['promo_settings'];
        $this->db->where('aktif', 1);
        $this->db->order_by('nama', 'ASC');
        $data['pelanggan'] = $this->db->get('m_pelanggan')->result();
        $data['kategori'] = $package_data['kategori'];
        $data['tipe'] = $package_data['tipe'];
        $data['paket'] = $package_data['paket'];

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
        $customer_notes = trim((string) $this->input->post('customer_notes'));

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
                'customer_notes' => $customer_notes,
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
                if (!empty($item['customer_notes'])) {
                    $promo_html .= '<small class="d-block text-muted mt-1"><i class="fas fa-clipboard-list me-1"></i>Catatan: ' . htmlspecialchars($item['customer_notes'], ENT_QUOTES, 'UTF-8') . '</small>';
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

        $pelanggan_aktif = $this->db
            ->where('id', (int) $id_pelanggan)
            ->where('aktif', 1)
            ->get('m_pelanggan')
            ->row();

        if (!$pelanggan_aktif) {
            $this->session->set_flashdata('error', 'Pelanggan yang dipilih tidak aktif atau tidak ditemukan.');
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
        $invoice = $this->generate_unique_invoice_code();

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

        foreach ($cart as $item) {
            $data_detail[] = [
                'id_transaksi' => $id_transaksi,
                'id_paket' => $item['id'],
                'qty' => $item['charged_qty'],
                'harga' => $item['harga'],
                'keterangan' => $this->build_promo_keterangan($item)
            ];
        }

        $this->db->insert_batch('transaksi_detail', $data_detail);

        $pelanggan = $this->db->get_where('m_pelanggan', ['id' => $id_pelanggan])->row();
        $wa_link = "";

        if ($pelanggan && !empty($pelanggan->no_hp)) {
            $wa_link = $this->build_confirmation_wa_link(
                $pelanggan->no_hp,
                $invoice,
                $pelanggan->nama,
                date('Y-m-d H:i:s'),
                $tgl_selesai,
                $cart,
                $total_tagihan
            );
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

        $data['detail'] = $this->get_transaction_details($data['transaksi']->id, true);
        $data['active_detail'] = array_values(array_filter(
            $data['detail'],
            static function ($detail) {
                return empty($detail->batal);
            }
        ));
        $data['can_add_items'] = $this->can_add_items_to_transaction($data['transaksi']);
        $data['can_modify_items'] = $this->can_modify_transaction_items($data['transaksi']);
        $data['add_item_block_reason'] = $this->get_add_item_block_reason($data['transaksi']);

        $data['wa_contact_link'] = '';
        $normalized_phone = $this->normalize_whatsapp_number($data['transaksi']->no_hp ?? '');
        if ($normalized_phone !== '') {
            $data['wa_contact_link'] = 'https://wa.me/' . $normalized_phone;
        }

        $total_tagihan = 0;
        foreach ($data['active_detail'] as $detail_row) {
            $total_tagihan += (float) ($detail_row->subtotal ?? 0);
        }

        $data['wa_confirmation_link'] = $this->build_confirmation_wa_link(
            $data['transaksi']->no_hp ?? '',
            $data['transaksi']->kode_invoice,
            $data['transaksi']->nama_pelanggan,
            $data['transaksi']->tgl_masuk,
            $data['transaksi']->batas_waktu,
            $data['active_detail'],
            $total_tagihan
        );

        $package_data = $this->load_package_form_data();
        $data['promo_settings'] = $package_data['promo_settings'];
        $data['kategori'] = $package_data['kategori'];
        $data['tipe'] = $package_data['tipe'];
        $data['paket'] = $package_data['paket'];

        $this->db->where('is_active', 1);
        $data['metode_bayar'] = $this->db->get('m_metode_bayar')->result();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('transaksi/detail', $data);
        $this->load->view('templates/footer');
    }

    public function update_catatan_item()
    {
        $kode_invoice = $this->input->post('kode_invoice');
        $detail_id = (int) $this->input->post('detail_id');
        $customer_notes = trim((string) $this->input->post('customer_notes'));

        $this->db->select('id, kode_invoice, status, dibayar');
        $this->db->from('transaksi');
        $this->db->where('kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if (!$this->can_modify_transaction_items($trx)) {
            $this->session->set_flashdata('error', 'Catatan item hanya bisa diedit saat transaksi masih Baru dan belum dibayar.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $detail = $this->db->get_where('transaksi_detail', [
            'id' => $detail_id,
            'id_transaksi' => $trx->id
        ])->row();

        if (!$detail) {
            $this->session->set_flashdata('error', 'Item transaksi tidak ditemukan.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if (!empty($detail->batal)) {
            $this->session->set_flashdata('error', 'Item yang sudah dibatalkan tidak bisa diedit lagi.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->db->where('id', $detail_id);
        $this->db->update('transaksi_detail', [
            'keterangan' => $this->merge_keterangan_notes($detail->keterangan, $customer_notes)
        ]);

        $this->session->set_flashdata('success', 'Catatan item berhasil diperbarui.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function update_detail_item()
    {
        $kode_invoice = $this->input->post('kode_invoice');
        $detail_id = (int) $this->input->post('detail_id');
        $qty = $this->input->post('qty');
        $customer_notes = trim((string) $this->input->post('customer_notes'));

        $this->db->select('id, kode_invoice, status, dibayar');
        $this->db->from('transaksi');
        $this->db->where('kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if (!$this->can_modify_transaction_items($trx)) {
            $this->session->set_flashdata('error', 'Item laundry hanya bisa diedit saat transaksi masih Baru dan belum dibayar.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $detail = $this->db->get_where('transaksi_detail', [
            'id' => $detail_id,
            'id_transaksi' => $trx->id
        ])->row();

        if (!$detail) {
            $this->session->set_flashdata('error', 'Item transaksi tidak ditemukan.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if (!empty($detail->batal)) {
            $this->session->set_flashdata('error', 'Item yang sudah dibatalkan tidak bisa diedit lagi.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $paket = $this->get_package_with_meta($detail->id_paket);
        if (!$paket) {
            $this->session->set_flashdata('error', 'Paket laundry untuk item ini tidak ditemukan.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $paket->harga = (int) $detail->harga;

        $promo = $this->parse_promo_keterangan($detail->keterangan ?? '');
        $promo_requested = is_array($promo) && !empty($promo['promo_type']);
        $item = $this->build_transaction_detail_item($paket, $qty, $promo_requested, $customer_notes);

        if ((float) $item['qty'] <= 0) {
            $this->session->set_flashdata('error', 'Jumlah bawaan harus lebih dari 0.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->db->where('id', $detail_id);
        $this->db->update('transaksi_detail', [
            'qty' => $item['charged_qty'],
            'harga' => $item['harga'],
            'keterangan' => $this->build_promo_keterangan($item)
        ]);

        $this->session->set_flashdata('success', 'Item laundry berhasil diperbarui.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function batal_detail_item()
    {
        $kode_invoice = $this->input->post('kode_invoice');
        $detail_id = (int) $this->input->post('detail_id');

        $this->db->select('id, kode_invoice, status, dibayar');
        $this->db->from('transaksi');
        $this->db->where('kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if (!$this->can_modify_transaction_items($trx)) {
            $this->session->set_flashdata('error', 'Item laundry hanya bisa dibatalkan saat transaksi masih Baru dan belum dibayar.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $detail = $this->db->get_where('transaksi_detail', [
            'id' => $detail_id,
            'id_transaksi' => $trx->id
        ])->row();

        if (!$detail) {
            $this->session->set_flashdata('error', 'Item transaksi tidak ditemukan.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if (!empty($detail->batal)) {
            $this->session->set_flashdata('error', 'Item ini sudah dibatalkan sebelumnya.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if ($this->count_active_transaction_details($trx->id) <= 1) {
            $this->session->set_flashdata('error', 'Minimal harus ada satu item aktif di dalam transaksi. Batalkan item terakhir tidak diizinkan.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->db->where('id', $detail_id);
        $this->db->update('transaksi_detail', [
            'batal' => 1,
            'batal_at' => date('Y-m-d H:i:s'),
            'batal_by' => (int) $this->session->userdata('user_id')
        ]);

        $this->sync_transaction_deadline($trx->id);

        $this->session->set_flashdata('success', 'Item laundry berhasil dibatalkan tanpa menghapus histori.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function tambah_item($kode_invoice)
    {
        $this->db->select('transaksi.id, transaksi.kode_invoice, transaksi.status, transaksi.dibayar');
        $this->db->from('transaksi');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if (!$this->can_add_items_to_transaction($trx)) {
            $this->session->set_flashdata('error', $this->get_add_item_block_reason($trx));
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $id_paket = (int) $this->input->post('id_paket');
        $qty = $this->input->post('qty');
        $promo_requested = $this->input->post('promo_cuci_3kg') == '1';
        $customer_notes = trim((string) $this->input->post('customer_notes'));

        if ($id_paket <= 0 || $qty === null || $qty === '') {
            $this->session->set_flashdata('error', 'Pilih paket laundry dan isi jumlah bawaan terlebih dahulu.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $paket = $this->get_package_with_meta($id_paket);

        if (!$paket) {
            $this->session->set_flashdata('error', 'Paket laundry tidak ditemukan.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $item = $this->build_transaction_detail_item($paket, $qty, $promo_requested, $customer_notes);

        if ((float) $item['qty'] <= 0) {
            $this->session->set_flashdata('error', 'Jumlah bawaan harus lebih dari 0.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->db->insert('transaksi_detail', [
            'id_transaksi' => $trx->id,
            'id_paket' => $paket->id_paket_laundry,
            'qty' => $item['charged_qty'],
            'harga' => $item['harga'],
            'keterangan' => $this->build_promo_keterangan($item)
        ]);

        $this->sync_transaction_deadline($trx->id);

        $this->session->set_flashdata('success', 'Item laundry berhasil ditambahkan ke nota ini.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function update_status()
    {
        $kode_invoice = $this->input->post('kode_invoice');
        $status_baru  = $this->input->post('status');
        $manual_statuses = ['Baru', 'Proses', 'Selesai'];

        if (!in_array($status_baru, $manual_statuses, true)) {
            $this->session->set_flashdata('error', 'Status laundry tidak valid.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->db->select('id, status, dibayar, tgl_selesai, tgl_diambil');
        $this->db->from('transaksi');
        $this->db->where('kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if ((string) $trx->status === 'Diambil') {
            $this->session->set_flashdata('error', 'Transaksi yang sudah diambil tidak bisa diubah status pengerjaannya lagi.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $data_update = ['status' => $status_baru];

        if ($status_baru === 'Selesai') {
            if (empty($trx->tgl_selesai)) {
                $data_update['tgl_selesai'] = date('Y-m-d H:i:s');
            }
        } elseif (!empty($trx->tgl_selesai)) {
            $data_update['tgl_selesai'] = null;
        }

        $this->db->where('kode_invoice', $kode_invoice);
        $this->db->update('transaksi', $data_update);

        $this->session->set_flashdata('success', 'Status Laundry berhasil diupdate menjadi: ' . strtoupper($status_baru));
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function bayar_tagihan($kode_invoice)
    {
        $id_metode_bayar = $this->input->post('id_metode_bayar');
        $tgl_bayar = date('Y-m-d H:i:s');

        $this->db->select('id, kode_invoice, status, dibayar, tgl_bayar');
        $this->db->from('transaksi');
        $this->db->where('kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if ((string) $trx->dibayar === 'Sudah Dibayar') {
            $this->session->set_flashdata('error', 'Transaksi ini sudah tercatat lunas.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if (empty($id_metode_bayar)) {
            $this->session->set_flashdata('error', 'Pilih metode pembayaran terlebih dahulu.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $data_update = [
            'dibayar' => $this->allowed_payment_statuses[1],
            'tgl_bayar' => $tgl_bayar,
            'id_metode_bayar' => $id_metode_bayar
        ];

        $this->db->where('kode_invoice', $kode_invoice);
        $this->db->update('transaksi', $data_update);

        $this->session->set_flashdata('success', 'Pembayaran berhasil dicatat. Status pengambilan tetap terpisah.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function tandai_diambil($kode_invoice)
    {
        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.no_hp');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if ((string) $trx->dibayar !== 'Sudah Dibayar') {
            $this->session->set_flashdata('error', 'Transaksi harus lunas terlebih dahulu sebelum ditandai sudah diambil.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if ((string) $trx->status !== 'Selesai' && (string) $trx->status !== 'Diambil') {
            $this->session->set_flashdata('error', 'Laundry hanya bisa ditandai diambil setelah status pengerjaan selesai.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if ((string) $trx->status === 'Diambil') {
            $this->session->set_flashdata('success', 'Transaksi ini sudah lebih dulu ditandai diambil.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $data_update = [
            'status' => 'Diambil',
            'tgl_diambil' => date('Y-m-d H:i:s')
        ];

        if (empty($trx->tgl_selesai)) {
            $data_update['tgl_selesai'] = date('Y-m-d H:i:s');
        }

        $this->db->where('kode_invoice', $kode_invoice);
        $this->db->update('transaksi', $data_update);

        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.no_hp');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        $details = $this->get_active_transaction_details($trx->id);

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
                $item_note_text = $this->build_item_note_text(
                    !empty($d->promo_applied),
                    $d->charged_qty ?? 0,
                    'Kg/Pcs',
                    $d->customer_notes ?? ''
                );

                $list_item_wa .= '- ' . strtoupper($d->nama_paket) . ', ' . $d->qty_label . " Kg/Pcs%0A";
                $list_item_wa .= 'Ket : ' . rawurlencode($item_note_text) . "%0A";
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
        $this->session->set_flashdata('success', 'Cucian berhasil ditandai sudah diambil.');

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

        $data['detail'] = $this->get_active_transaction_details($data['transaksi']->id);

        $data['company'] = $this->company;
        $this->load->view('transaksi/cetak', $data);
    }
}
