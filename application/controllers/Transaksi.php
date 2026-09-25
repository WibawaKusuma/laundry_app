<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Alur tagihan & benefit (lihat README bagian "Alur Transaksi", "Promo dan Reward", "Snapshot Tagihan"):
 *
 * 1. simpan()      : aturan promo yang aktif dikunci ke nota; jika poin pelanggan sudah >= batas dan nota
 *                    punya item Cuci Komplit Reguler/Satu hari, poin langsung di-reserve untuk reward.
 *                    Snapshot tagihan dihitung dan disimpan.
 * 2. tambah/edit/batal item : snapshot dihitung ulang dari data terkunci yang sama.
 * 3. detail()/cetak(): HANYA membaca, tidak pernah menulis snapshot.
 * 4. bayar_tagihan(): tidak menghitung benefit baru; hanya memvalidasi snapshot lalu mencatat lunas.
 * 5. Poin +1 diberikan saat nota selesai & lunas (sinkron_poin_member).
 */
class Transaksi extends MY_Controller
{
    private $allowed_statuses = ['Baru', 'Proses', 'Selesai', 'Diambil', 'Dibatalkan'];
    private $allowed_payment_statuses = ['Belum Dibayar', 'Sudah Dibayar'];
    private $cancelled_status = 'Dibatalkan';
    private $reward_default_batas_poin = 8;
    private $reward_default_gratis_qty = 3;
    private $promo_opening_default_gratis_qty = 3;
    private $promo_daily_default_min_kg = 7;
    private $promo_daily_default_gratis_qty = 1;
    private $promo_sepatu_default_min_pasang = 5;
    private $promo_sepatu_default_gratis_qty = 1;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('laundry_service_rules');
        $this->load->library('laundry_benefit_calculator');
        $this->load->library('laundry_notification_builder');
        $this->load->model('Transaksi_model');

        if (empty($this->session->userdata('role'))) {
            redirect('auth/login');
        }
    }

    // ------------------------------------------------------------------
    // Pengaturan promo & reward (dibaca dari tabel config)
    // ------------------------------------------------------------------

    private function config_number($key, $default)
    {
        $value = isset($this->company[$key]) ? (float) $this->company[$key] : 0;

        return $value > 0 ? $value : (float) $default;
    }

    private function config_label($key, $default)
    {
        $label = trim((string) ($this->company[$key] ?? ''));

        return $label !== '' ? $label : $default;
    }

    private function get_promo_opening_settings()
    {
        return [
            'is_enabled' => (string) ($this->company['promo_opening_aktif'] ?? '0') === '1',
            'gratis_qty' => $this->config_number('promo_opening_gratis_qty', $this->promo_opening_default_gratis_qty),
            'label' => $this->config_label('promo_opening_label', 'Promo Opening Gratis Cuci 3 Kg'),
        ];
    }

    private function get_promo_daily_settings()
    {
        return [
            'is_enabled' => (string) ($this->company['promo_daily_aktif'] ?? '0') === '1',
            'min_kg' => $this->config_number('promo_daily_min_kg', $this->promo_daily_default_min_kg),
            'gratis_qty' => $this->config_number('promo_daily_gratis_qty', $this->promo_daily_default_gratis_qty),
            'label' => $this->config_label('promo_daily_label', 'Promo Daily Minimal 7 Kg Gratis 1 Kg'),
        ];
    }

    private function get_promo_sepatu_settings()
    {
        return [
            'is_enabled' => (string) ($this->company['promo_sepatu_aktif'] ?? '0') === '1',
            'min_pasang' => $this->config_number('promo_sepatu_min_pasang', $this->promo_sepatu_default_min_pasang),
            'gratis_qty' => $this->config_number('promo_sepatu_gratis_qty', $this->promo_sepatu_default_gratis_qty),
            'label' => $this->config_label('promo_sepatu_label', 'Promo Sepatu 5 Pasang Gratis 1 Pasang'),
        ];
    }

    private function get_member_settings()
    {
        $batas_poin = (int) $this->config_number('reward_member_batas_poin', $this->reward_default_batas_poin);
        $gratis_qty = $this->config_number('reward_member_gratis_qty', $this->reward_default_gratis_qty);

        return [
            'batas_poin' => max(1, $batas_poin),
            'gratis_qty' => $gratis_qty,
            'label_reward' => 'Reward Member Gratis Cuci ' . $this->laundry_service_rules->format_qty($gratis_qty) . ' Kg Cuci Komplit Reguler / Satu Hari',
        ];
    }

    private function get_promo_aktif_summary()
    {
        $opening = $this->get_promo_opening_settings();
        $daily = $this->get_promo_daily_settings();
        $sepatu = $this->get_promo_sepatu_settings();
        $aktif = [];

        if ($opening['is_enabled']) {
            $aktif[] = $opening['label'] . ' (otomatis per nota, Cuci Komplit kg lebih dari ' . $this->laundry_service_rules->format_qty($opening['gratis_qty']) . ' kg)';
        }
        if ($daily['is_enabled']) {
            $aktif[] = $daily['label'];
        }
        if ($sepatu['is_enabled']) {
            $aktif[] = $sepatu['label'] . ' (khusus nota sepatu)';
        }

        return $aktif;
    }

    /**
     * Aturan promo yang dikunci ke nota saat dibuat, berdasarkan promo yang AKTIF saat itu
     * (bukan berdasarkan isi keranjang). Nota alas kaki hanya bisa memakai Promo Sepatu,
     * nota cucian memakai Promo Opening atau Promo Daily (keduanya tidak boleh aktif bersamaan).
     * Pelanggan mitra tidak mendapat aturan promo apa pun.
     */
    private function siapkan_aturan_promo_order($is_nota_alas_kaki, $is_mitra = false)
    {
        $kosong = [
            'aturan_promo_tipe' => null,
            'aturan_promo_min_qty' => 0,
            'aturan_promo_gratis_qty' => 0,
            'aturan_promo_unit' => null,
            'aturan_promo_label' => null,
        ];

        if ($is_mitra) {
            return $kosong;
        }

        if ($is_nota_alas_kaki) {
            $sepatu = $this->get_promo_sepatu_settings();

            return $sepatu['is_enabled'] ? [
                'aturan_promo_tipe' => 'promo_sepatu',
                'aturan_promo_min_qty' => (float) $sepatu['min_pasang'],
                'aturan_promo_gratis_qty' => (float) $sepatu['gratis_qty'],
                'aturan_promo_unit' => 'pasang',
                'aturan_promo_label' => $sepatu['label'],
            ] : $kosong;
        }

        $opening = $this->get_promo_opening_settings();
        if ($opening['is_enabled']) {
            return [
                'aturan_promo_tipe' => 'promo_opening',
                'aturan_promo_min_qty' => (float) $opening['gratis_qty'],
                'aturan_promo_gratis_qty' => (float) $opening['gratis_qty'],
                'aturan_promo_unit' => 'kg',
                'aturan_promo_label' => $opening['label'],
            ];
        }

        $daily = $this->get_promo_daily_settings();
        if ($daily['is_enabled']) {
            return [
                'aturan_promo_tipe' => 'promo_daily',
                'aturan_promo_min_qty' => (float) $daily['min_kg'],
                'aturan_promo_gratis_qty' => (float) $daily['gratis_qty'],
                'aturan_promo_unit' => 'kg',
                'aturan_promo_label' => $daily['label'],
            ];
        }

        return $kosong;
    }

    // ------------------------------------------------------------------
    // Klasifikasi item
    // ------------------------------------------------------------------

    private function item_field($item, $key)
    {
        if (is_array($item)) {
            return (string) ($item[$key] ?? '');
        }

        return (string) ($item->$key ?? '');
    }

    private function is_item_alas_kaki($item)
    {
        return $this->laundry_service_rules->is_item_alas_kaki($this->item_field($item, 'nama_satuan'), $this->item_field($item, 'nama_paket'));
    }

    private function is_pelanggan_mitra($pelanggan)
    {
        return $pelanggan && (string) ($pelanggan->jenis_pelanggan ?? 'umum') === 'mitra';
    }

    private function nota_dapat_poin($items)
    {
        foreach ((array) $items as $item) {
            if ($this->laundry_service_rules->is_item_dapat_poin($this->item_field($item, 'nama_satuan'), $this->item_field($item, 'nama_tipe'), $this->item_field($item, 'nama_paket'))) {
                return true;
            }
        }

        return false;
    }

    private function nota_punya_layanan_reward($items)
    {
        foreach ((array) $items as $item) {
            if ($this->laundry_service_rules->is_reward_member_service_eligible($this->item_field($item, 'nama_satuan'), $this->item_field($item, 'nama_tipe'), $this->item_field($item, 'nama_paket'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nota alas kaki (sepatu/sandal) wajib terpisah dari nota cucian.
     * Mengembalikan pesan penolakan, atau null jika boleh digabung.
     */
    private function cek_campuran_alas_kaki($items_existing, $item_baru)
    {
        $items_existing = array_values((array) $items_existing);
        if (empty($items_existing)) {
            return null;
        }

        $baru_alas_kaki = $this->is_item_alas_kaki($item_baru);
        $nota_alas_kaki = $this->is_item_alas_kaki($items_existing[0]);

        if ($baru_alas_kaki === $nota_alas_kaki) {
            return null;
        }

        return $baru_alas_kaki
            ? 'Sepatu/sandal harus dibuat di nota terpisah dari cucian. Simpan nota cucian ini dulu, lalu buat nota baru khusus sepatu.'
            : 'Nota ini khusus sepatu/sandal. Cucian harus dibuat di nota terpisah.';
    }

    private function is_nota_campuran_alas_kaki($items)
    {
        $ada_alas_kaki = false;
        $ada_lainnya = false;

        foreach ((array) $items as $item) {
            if ($this->is_item_alas_kaki($item)) {
                $ada_alas_kaki = true;
            } else {
                $ada_lainnya = true;
            }
        }

        return $ada_alas_kaki && $ada_lainnya;
    }

    // ------------------------------------------------------------------
    // Pelanggan & keranjang
    // ------------------------------------------------------------------

    private function get_poin_member_pelanggan($id_pelanggan)
    {
        $id_pelanggan = (int) $id_pelanggan;
        if ($id_pelanggan <= 0) {
            return 0;
        }

        $pelanggan = $this->Transaksi_model->get_pelanggan_member_data($id_pelanggan);

        return max(0, (int) ($pelanggan->poin_member ?? 0));
    }

    private function get_cart_customer_id()
    {
        return (int) $this->session->userdata('cart_customer_id');
    }

    private function set_cart_customer_id($id_pelanggan)
    {
        $this->session->set_userdata('cart_customer_id', (int) $id_pelanggan);
    }

    private function clear_cart_session()
    {
        $this->session->unset_userdata('cart');
        $this->session->unset_userdata('cart_customer_id');
    }

    private function normalize_cart_item($item)
    {
        $pricing = $this->laundry_service_rules->calculate_item_pricing($item['harga'] ?? 0, $item['qty'] ?? 0);

        $item['qty'] = $pricing['actual_qty'];
        $item['charged_qty'] = $pricing['charged_qty'];
        $item['subtotal'] = $pricing['subtotal'];
        $item['promo_applied'] = false;
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

    private function build_transaction_detail_item($paket, $qty, $customer_notes = '')
    {
        $pricing = $this->laundry_service_rules->calculate_item_pricing($paket->harga, $qty);

        return [
            'id' => $paket->id_paket_laundry,
            'nama_paket' => $paket->nama_paket,
            'nama_satuan' => $paket->nama_satuan,
            'nama_kategori' => $paket->nama_kategori,
            'nama_tipe' => $paket->nama_tipe,
            'harga' => $paket->harga,
            'qty' => $pricing['actual_qty'],
            'charged_qty' => $pricing['charged_qty'],
            'promo_applied' => false,
            'customer_notes' => trim((string) $customer_notes),
            'subtotal' => $pricing['subtotal'],
        ];
    }

    // ------------------------------------------------------------------
    // Tagihan
    // ------------------------------------------------------------------

    private function is_nota_terbuka($trx)
    {
        return $trx
            && (string) ($trx->dibayar ?? '') === 'Belum Dibayar'
            && (string) ($trx->status ?? '') !== $this->cancelled_status;
    }

    /**
     * Ringkasan tagihan untuk ditampilkan.
     * - Nota terbuka: dihitung dari data terkunci (hasilnya selalu sama dengan snapshot tersimpan,
     *   kecuali data lama/rusak; ditandai snapshot_sinkron = false dan pembayaran diblokir).
     * - Nota lunas/batal: dibaca apa adanya dari snapshot.
     */
    private function hitung_ringkasan_transaksi($trx, array $active_details)
    {
        if (!$this->is_nota_terbuka($trx)) {
            $ringkasan = $this->laundry_benefit_calculator->ringkasan_dari_snapshot($trx, $active_details);
            $ringkasan['snapshot_sinkron'] = true;

            return $ringkasan;
        }

        $ringkasan = $this->laundry_benefit_calculator->hitung($trx, $active_details);
        $ringkasan['snapshot_sinkron'] = empty($trx->snapshot_tagihan_at)
            || (int) ($trx->total_akhir ?? 0) === (int) round((float) $ringkasan['total_akhir']);

        return $ringkasan;
    }

    /**
     * Hitung ulang dan simpan snapshot setelah isi nota berubah (hanya nota terbuka).
     * Jika reward sudah di-reserve tapi item Cuci Komplit Reguler/Satu hari sudah tidak ada,
     * poin reward dikembalikan ke pelanggan.
     */
    private function simpan_snapshot_tagihan($id_transaksi)
    {
        $trx = $this->Transaksi_model->get_transaction_by_id($id_transaksi);
        if (!$this->is_nota_terbuka($trx)) {
            return null;
        }

        $details = $this->get_active_transaction_details($trx->id);

        if ((int) ($trx->reward_poin_dipakai ?? 0) > 0 && !$this->nota_punya_layanan_reward($details)) {
            $this->Transaksi_model->release_reward_reservation(
                $trx->id,
                $this->session->userdata('user_id'),
                'Reward batal: item Cuci Komplit Reguler/Satu hari dihapus dari nota ' . $trx->kode_invoice
            );
            $trx = $this->Transaksi_model->get_transaction_by_id($id_transaksi);
        }

        $ringkasan = $this->laundry_benefit_calculator->hitung($trx, $details);
        $this->Transaksi_model->update_billing_snapshot($trx->id, $this->laundry_benefit_calculator->snapshot_fields($ringkasan));

        return $ringkasan;
    }

    private function sinkron_poin_member($id_transaksi)
    {
        $trx = $this->Transaksi_model->get_point_sync_transaction($id_transaksi);

        if (
            !$trx
            || (int) ($trx->id_pelanggan ?? 0) <= 0
            || !in_array((string) ($trx->status ?? ''), ['Selesai', 'Diambil'], true)
            || (string) ($trx->dibayar ?? '') !== 'Sudah Dibayar'
            || !empty($trx->poin_diberikan_pada)
            || (string) ($trx->jenis_pelanggan ?? 'umum') === 'mitra'
            || !empty($trx->reward_member_dipakai)
            || (int) ($trx->reward_poin_dipakai ?? 0) > 0
            || !$this->nota_dapat_poin($this->get_active_transaction_details($trx->id))
        ) {
            return false;
        }

        return $this->Transaksi_model->sync_member_point($trx->id, $trx->id_pelanggan, $this->session->userdata('user_id'));
    }

    // ------------------------------------------------------------------
    // WhatsApp & badge
    // ------------------------------------------------------------------

    private function build_wa_meta($trx, array $ringkasan, $details, $mode, array $extra = [])
    {
        $member = $this->get_member_settings();

        return array_merge([
            'mode' => $mode,
            'status' => $trx->status ?? '',
            'dibayar' => $trx->dibayar ?? '',
            'nama_metode_bayar' => $trx->nama_metode_bayar ?? '',
            'tgl_bayar' => $trx->tgl_bayar ?? '',
            'tgl_diambil' => $trx->tgl_diambil ?? '',
            'poin_member_pelanggan' => (int) ($trx->poin_member_pelanggan ?? 0),
            'poin_diberikan_pada' => $trx->poin_diberikan_pada ?? null,
            'is_mitra' => $this->laundry_benefit_calculator->is_nota_mitra($trx),
            'dapat_poin' => $this->nota_dapat_poin($details),
            'batas_poin' => $member['batas_poin'],
            'total_akhir' => (float) $ringkasan['total_akhir'],
            'benefit_tipe' => $ringkasan['benefit_tipe'] ?? '',
            'reward_member_dipakai' => !empty($ringkasan['reward_dipakai']),
            'reward_gratis_qty' => (float) $ringkasan['reward_gratis_qty'],
            'reward_potongan' => (int) $ringkasan['reward_potongan'],
            'reward_poin_dipakai' => (int) ($ringkasan['reward_poin_dipakai'] ?? 0),
            'promo_gratis_dipakai' => !empty($ringkasan['promo_dipakai']),
            'promo_gratis_qty' => (float) $ringkasan['promo_gratis_qty'],
            'promo_gratis_potongan' => (int) $ringkasan['promo_gratis_potongan'],
            'promo_gratis_keterangan' => $ringkasan['promo_gratis_keterangan'] ?? '',
            'promo_gratis_label' => $ringkasan['promo_gratis_label'] ?? '',
            'promo_gratis_unit' => $ringkasan['promo_gratis_unit'] ?? 'kg',
        ], $extra);
    }

    private function siapkan_badge_benefit_transaksi($transaksi, $active_detail, array $ringkasan)
    {
        if (!$transaksi || $this->laundry_benefit_calculator->is_nota_mitra($transaksi)) {
            return null;
        }

        $member = $this->get_member_settings();
        $poin_pelanggan = max(0, (int) ($transaksi->poin_member_pelanggan ?? 0));

        if (!empty($ringkasan['reward_dipakai'])) {
            return [
                'label' => 'Reward Member',
                'icon' => 'fas fa-gift',
                'class' => 'trx-benefit-badge-success',
            ];
        }

        if (!empty($ringkasan['promo_dipakai'])) {
            return [
                'label' => 'Promo Gratis',
                'icon' => 'fas fa-tags',
                'class' => 'trx-benefit-badge-warning',
            ];
        }

        if (!empty($transaksi->poin_diberikan_pada)) {
            return [
                'label' => 'Poin Sudah Masuk',
                'icon' => 'fas fa-check-circle',
                'class' => 'trx-benefit-badge-info',
            ];
        }

        if ($poin_pelanggan >= $member['batas_poin']) {
            return [
                'label' => 'Reward Tersimpan (' . $poin_pelanggan . ' poin)',
                'icon' => 'fas fa-coins',
                'class' => 'trx-benefit-badge-stored',
            ];
        }

        if ($this->nota_dapat_poin($active_detail) && (string) ($transaksi->status ?? '') !== $this->cancelled_status) {
            return [
                'label' => 'Dapat Poin',
                'icon' => 'fas fa-star',
                'class' => 'trx-benefit-badge-primary',
            ];
        }

        return null;
    }

    // ------------------------------------------------------------------
    // Helper detail & status
    // ------------------------------------------------------------------

    private function get_package_with_meta($id_paket)
    {
        return $this->Transaksi_model->get_package_with_meta($id_paket);
    }

    private function get_transaction_details($transaksi_id, $include_cancelled = false)
    {
        return $this->laundry_service_rules->enrich_detail_rows($this->Transaksi_model->get_transaction_details_raw($transaksi_id, $include_cancelled));
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
        return $this->Transaksi_model->count_active_transaction_details($transaksi_id);
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
            $exists = $this->Transaksi_model->invoice_exists($invoice);
        } while ($exists && $attempt < 20);

        if ($exists) {
            throw new RuntimeException('Gagal membuat nomor invoice unik.');
        }

        return $invoice;
    }

    private function load_package_form_data()
    {
        $data = [];
        $data['promo_aktif'] = $this->get_promo_aktif_summary();
        $data['kategori'] = $this->Transaksi_model->get_kategori();
        $data['tipe'] = $this->Transaksi_model->get_tipe();
        $data['paket'] = $this->Transaksi_model->get_paket_form_options();

        return $data;
    }

    private function can_add_items_to_transaction($transaksi)
    {
        if (!$transaksi) {
            return false;
        }

        if ((string) $transaksi->status === $this->cancelled_status) {
            return false;
        }

        return (string) $transaksi->status === 'Baru'
            && (string) $transaksi->dibayar === 'Belum Dibayar';
    }

    private function can_cancel_transaction($transaksi)
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

        if ((string) $transaksi->status === $this->cancelled_status) {
            return 'Transaksi sudah dibatalkan.';
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
        $trx = $this->Transaksi_model->get_deadline_source($transaksi_id);

        if (!$trx) {
            return;
        }

        $max_jam = $this->Transaksi_model->get_max_active_detail_duration($transaksi_id);
        if ($max_jam <= 0) {
            $max_jam = 24;
        }

        $batas_waktu = date('Y-m-d H:i:s', strtotime($trx->tgl_masuk . " +$max_jam hours"));

        $this->Transaksi_model->update_deadline($transaksi_id, $batas_waktu);
    }

    // ------------------------------------------------------------------
    // Halaman & aksi
    // ------------------------------------------------------------------

    public function index()
    {
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');
        $status_bayar = $this->input->get('status_bayar');

        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal  = date('Y-m-01');
            $tgl_akhir = date('Y-m-d');
        }

        if (!in_array($status_bayar, ['semua', 'sudah', 'belum'], true)) {
            $status_bayar = 'belum';
        }

        $data['transaksi'] = $this->Transaksi_model->get_index_by_periode($tgl_awal, $tgl_akhir, $status_bayar);

        $ringkasan = [
            'total' => count($data['transaksi']),
            'belum_lunas' => 0,
            'siap_diambil' => 0,
            'lunas_belum_diambil' => 0,
            'terlambat' => 0,
        ];

        $now = time();
        foreach ($data['transaksi'] as $trx) {
            if ((string) $trx->status === $this->cancelled_status) {
                continue;
            }

            if ((string) $trx->dibayar !== 'Sudah Dibayar') {
                $ringkasan['belum_lunas']++;
            }

            if ((string) $trx->status === 'Selesai') {
                $ringkasan['siap_diambil']++;
            }

            if ((string) $trx->status === 'Selesai' && (string) $trx->dibayar === 'Sudah Dibayar') {
                $ringkasan['lunas_belum_diambil']++;
            }

            if ((string) $trx->status !== 'Diambil' && (string) $trx->status !== $this->cancelled_status && !empty($trx->batas_waktu) && strtotime($trx->batas_waktu) < $now) {
                $ringkasan['terlambat']++;
            }
        }

        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;
        $data['status_bayar'] = $status_bayar;
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
        $data['promo_aktif'] = $package_data['promo_aktif'];
        $data['member_settings'] = $this->get_member_settings();
        $data['pelanggan'] = $this->Transaksi_model->get_pelanggan_aktif();
        $data['kategori'] = $package_data['kategori'];
        $data['tipe'] = $package_data['tipe'];
        $data['paket'] = $package_data['paket'];
        $data['selected_pelanggan_id'] = $this->get_cart_customer_id();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('transaksi/form', $data);
        $this->load->view('templates/footer');
    }

    public function add_to_cart()
    {
        $id_paket = $this->input->post('id_paket');
        $qty = $this->input->post('qty');
        $id_pelanggan = (int) $this->input->post('id_pelanggan');
        $customer_notes = trim((string) $this->input->post('customer_notes'));

        if ($id_pelanggan <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Pilih pelanggan terlebih dahulu sebelum menambah item ke keranjang.'
            ]);
            return;
        }

        $existing_cart_customer_id = $this->get_cart_customer_id();
        if ($existing_cart_customer_id > 0 && $existing_cart_customer_id !== $id_pelanggan) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Keranjang sudah terikat ke pelanggan lain. Kosongkan keranjang terlebih dahulu jika ingin ganti pelanggan.'
            ]);
            return;
        }

        $paket = $this->Transaksi_model->get_package_with_meta($id_paket);

        if (!$paket) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Paket laundry tidak ditemukan.'
            ]);
            return;
        }

        $item = $this->build_transaction_detail_item($paket, $qty, $customer_notes);

        if ((float) $item['qty'] <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Jumlah bawaan harus lebih dari 0.'
            ]);
            return;
        }

        $cart = $this->session->userdata('cart');
        if (empty($cart) || !is_array($cart)) {
            $cart = [];
        }

        $pesan_campuran = $this->cek_campuran_alas_kaki($cart, $item);
        if ($pesan_campuran !== null) {
            echo json_encode([
                'status' => 'error',
                'message' => $pesan_campuran
            ]);
            return;
        }

        $cart_key = (string) $paket->id_paket_laundry;

        if (isset($cart[$cart_key])) {
            $cart[$cart_key]['qty'] += (float) $item['qty'];
            if ($item['customer_notes'] !== '') {
                $cart[$cart_key]['customer_notes'] = trim($cart[$cart_key]['customer_notes'] . ' | ' . $item['customer_notes'], ' |');
            }
            $cart[$cart_key] = $this->normalize_cart_item($cart[$cart_key]);
        } else {
            $cart[$cart_key] = $item;
        }

        $this->session->set_userdata('cart', $cart);
        $this->set_cart_customer_id($id_pelanggan);
        $this->get_normalized_cart(true);

        echo json_encode([
            'status' => 'success',
        ]);
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
                $qty_label = $this->laundry_service_rules->format_qty($item['qty']);
                $harga_label = 'Rp ' . number_format($item['harga'], 0, ',', '.');
                $notes_html = '';

                if (!empty($item['customer_notes'])) {
                    $notes_html .= '<small class="d-block text-muted mt-1"><i class="fas fa-clipboard-list me-1"></i>Catatan: ' . htmlspecialchars($item['customer_notes'], ENT_QUOTES, 'UTF-8') . '</small>';
                }

                $html .= '
                <tr>
                    <td>' . $no++ . '</td>
                    <td>' . $item['nama_paket'] . ' <small class="text-muted d-block">' . $item['nama_kategori'] . ' - ' . ($item['nama_tipe'] ?? '-') . '</small>' . $notes_html . '</td>
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
            'total_bayar' => number_format($total_bayar, 0, ',', '.'),
            'item_count' => count($cart),
            'cart_customer_id' => $this->get_cart_customer_id(),
        ]);
    }

    public function hapus_cart()
    {
        $id = $this->input->post('id');
        $cart = $this->session->userdata('cart');

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        if (empty($cart)) {
            $this->clear_cart_session();
        } else {
            $this->session->set_userdata('cart', $cart);
        }

        echo json_encode(['status' => 'success']);
    }

    public function simpan()
    {
        $id_pelanggan = (int) $this->input->post('id_pelanggan');
        $cart = $this->get_normalized_cart(true);

        if (empty($cart) || empty($id_pelanggan)) {
            $this->session->set_flashdata('error', 'Keranjang kosong atau Pelanggan belum dipilih!');
            redirect('transaksi/baru');
        }

        $cart_customer_id = $this->get_cart_customer_id();
        if ($cart_customer_id > 0 && $cart_customer_id !== $id_pelanggan) {
            $this->session->set_flashdata('error', 'Keranjang transaksi ini terikat ke pelanggan lain. Kosongkan keranjang terlebih dahulu jika ingin mengganti pelanggan.');
            redirect('transaksi/baru');
        }

        if ($this->is_nota_campuran_alas_kaki($cart)) {
            $this->session->set_flashdata('error', 'Sepatu/sandal harus dibuat di nota terpisah dari cucian. Hapus salah satu jenis item dari keranjang.');
            redirect('transaksi/baru');
        }

        $pelanggan_aktif = $this->Transaksi_model->get_pelanggan_aktif_by_id($id_pelanggan);

        if (!$pelanggan_aktif) {
            $this->session->set_flashdata('error', 'Pelanggan yang dipilih tidak aktif atau tidak ditemukan.');
            redirect('transaksi/baru');
        }

        $max_jam = 0;

        foreach ($cart as $item) {
            $paket_db = $this->Transaksi_model->get_paket_by_id($item['id']);

            if ($paket_db && $paket_db->durasi_jam > $max_jam) {
                $max_jam = $paket_db->durasi_jam;
            }
        }

        if ($max_jam == 0) {
            $max_jam = 24;
        }

        $tgl_masuk = date('Y-m-d H:i:s');
        $tgl_selesai = date('Y-m-d H:i:s', strtotime("+$max_jam hours"));
        $invoice = $this->generate_unique_invoice_code();
        $is_nota_alas_kaki = $this->is_item_alas_kaki(reset($cart));
        $is_mitra = $this->is_pelanggan_mitra($pelanggan_aktif);

        // Pelanggan mitra: jenis dikunci ke nota dan tidak ada aturan promo yang dikunci.
        $data_transaksi = array_merge([
            'kode_invoice' => $invoice,
            'id_pelanggan' => $id_pelanggan,
            'jenis_pelanggan' => $is_mitra ? 'mitra' : 'umum',
            'tgl_masuk' => $tgl_masuk,
            'batas_waktu' => $tgl_selesai,
            'status' => $this->allowed_statuses[0],
            'dibayar' => $this->allowed_payment_statuses[0],
            'id_user' => $this->session->userdata('user_id'),
        ], $this->siapkan_aturan_promo_order($is_nota_alas_kaki, $is_mitra));

        $data_detail = [];

        foreach ($cart as $item) {
            $data_detail[] = [
                'id_paket' => $item['id'],
                'qty' => $item['charged_qty'],
                'harga' => $item['harga'],
                'keterangan' => $this->laundry_service_rules->build_item_keterangan($item['customer_notes'] ?? ''),
            ];
        }

        // Reward diputuskan SEKALI di sini: poin sudah >= batas dan ada item Cuci Komplit Reguler/Satu hari.
        $member = $this->get_member_settings();
        $reward_request = null;
        if (!$is_mitra && (int) ($pelanggan_aktif->poin_member ?? 0) >= $member['batas_poin'] && $this->nota_punya_layanan_reward($cart)) {
            $reward_request = [
                'batas_poin' => $member['batas_poin'],
                'gratis_qty' => $member['gratis_qty'],
                'user_id' => $this->session->userdata('user_id'),
            ];
        }

        $create_result = $this->Transaksi_model->create_transaction_with_details($data_transaksi, $data_detail, $reward_request);

        if (!$create_result['success']) {
            $this->session->set_flashdata('error', 'Transaksi gagal disimpan. Silakan coba lagi.');
            redirect('transaksi/baru');
        }

        $ringkasan = $this->simpan_snapshot_tagihan($create_result['id_transaksi']);
        $transaksi_baru = $this->Transaksi_model->get_transaction_by_id($create_result['id_transaksi']);
        $wa_link = '';

        if ($transaksi_baru && !empty($transaksi_baru->no_hp) && is_array($ringkasan)) {
            $wa_link = $this->laundry_notification_builder->build_confirmation_wa_link(
                $this->company,
                $member['batas_poin'],
                $transaksi_baru->no_hp,
                $invoice,
                $transaksi_baru->nama_pelanggan,
                $tgl_masuk,
                $tgl_selesai,
                $cart,
                $ringkasan['total_normal'],
                $this->build_wa_meta($transaksi_baru, $ringkasan, $cart, 'awal', ['show_potential_poin' => true])
            );
        }

        $pesan_sukses = 'Transaksi Berhasil Disimpan!';
        if (!empty($create_result['reward_reserved'])) {
            $pesan_sukses .= ' Reward member (' . $member['batas_poin'] . ' poin) otomatis dipakai di nota ini.';
        }

        $this->clear_cart_session();
        $this->session->set_flashdata('wa_link', $wa_link);
        $this->session->set_flashdata('success', $pesan_sukses);

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

        $data['transaksi'] = $this->Transaksi_model->get_detail_transaction_by_invoice($kode_invoice);

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
        $data['can_cancel_transaction'] = $this->can_cancel_transaction($data['transaksi']);
        $data['add_item_block_reason'] = $this->get_add_item_block_reason($data['transaksi']);

        $data['wa_contact_link'] = '';
        $normalized_phone = $this->laundry_notification_builder->normalize_whatsapp_number($data['transaksi']->no_hp ?? '');
        if ($normalized_phone !== '') {
            $data['wa_contact_link'] = 'https://wa.me/' . $normalized_phone;
        }

        // Halaman detail hanya membaca; snapshot tidak pernah ditulis di sini.
        $data['ringkasan_pembayaran'] = $this->hitung_ringkasan_transaksi($data['transaksi'], $data['active_detail']);
        $data['benefit_badge'] = $this->siapkan_badge_benefit_transaksi($data['transaksi'], $data['active_detail'], $data['ringkasan_pembayaran']);
        $data['grand_total'] = $this->laundry_benefit_calculator->hitung_total_normal($data['active_detail']);

        $member = $this->get_member_settings();
        $data['wa_confirmation_link'] = (string) $data['transaksi']->status === $this->cancelled_status ? '' : $this->laundry_notification_builder->build_confirmation_wa_link(
            $this->company,
            $member['batas_poin'],
            $data['transaksi']->no_hp ?? '',
            $data['transaksi']->kode_invoice,
            $data['transaksi']->nama_pelanggan,
            $data['transaksi']->tgl_masuk,
            $data['transaksi']->batas_waktu,
            $data['active_detail'],
            $data['ringkasan_pembayaran']['total_normal'],
            $this->build_wa_meta($data['transaksi'], $data['ringkasan_pembayaran'], $data['active_detail'], 'detail', ['show_potential_poin' => true])
        );

        $package_data = $this->load_package_form_data();
        $data['promo_aktif'] = $package_data['promo_aktif'];
        $data['member_settings'] = $member;
        $data['kategori'] = $package_data['kategori'];
        $data['tipe'] = $package_data['tipe'];
        $data['paket'] = $package_data['paket'];

        $data['metode_bayar'] = $this->Transaksi_model->get_active_payment_methods();

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

        $trx = $this->Transaksi_model->get_basic_transaction_by_invoice($kode_invoice);

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if (!$this->can_modify_transaction_items($trx)) {
            $this->session->set_flashdata('error', 'Catatan item hanya bisa diedit saat transaksi masih Baru dan belum dibayar.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $detail = $this->Transaksi_model->get_detail_item($detail_id, $trx->id);

        if (!$detail) {
            $this->session->set_flashdata('error', 'Item transaksi tidak ditemukan.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if (!empty($detail->batal)) {
            $this->session->set_flashdata('error', 'Item yang sudah dibatalkan tidak bisa diedit lagi.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->Transaksi_model->update_detail_note(
            $detail_id,
            $this->laundry_service_rules->merge_keterangan_notes($detail->keterangan, $customer_notes)
        );

        $this->session->set_flashdata('success', 'Catatan item berhasil diperbarui.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function update_detail_item()
    {
        $kode_invoice = $this->input->post('kode_invoice');
        $detail_id = (int) $this->input->post('detail_id');
        $qty = $this->input->post('qty');
        $customer_notes = trim((string) $this->input->post('customer_notes'));

        $trx = $this->Transaksi_model->get_basic_transaction_by_invoice($kode_invoice);

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if (!$this->can_modify_transaction_items($trx)) {
            $this->session->set_flashdata('error', 'Item laundry hanya bisa diedit saat transaksi masih Baru dan belum dibayar.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $detail = $this->Transaksi_model->get_detail_item($detail_id, $trx->id);

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

        // Harga item tetap harga saat item dibuat.
        $paket->harga = (int) $detail->harga;
        $item = $this->build_transaction_detail_item($paket, $qty, $customer_notes);

        if ((float) $item['qty'] <= 0) {
            $this->session->set_flashdata('error', 'Jumlah bawaan harus lebih dari 0.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->Transaksi_model->update_detail_item($detail_id, [
            'charged_qty' => $item['charged_qty'],
            'harga' => $item['harga'],
            'keterangan' => $this->laundry_service_rules->build_item_keterangan($item['customer_notes']),
        ]);
        $this->simpan_snapshot_tagihan($trx->id);

        $this->session->set_flashdata('success', 'Item laundry berhasil diperbarui. Kirim ulang nota WhatsApp agar customer menerima total terbaru.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function batal_detail_item()
    {
        $kode_invoice = $this->input->post('kode_invoice');
        $detail_id = (int) $this->input->post('detail_id');

        $trx = $this->Transaksi_model->get_basic_transaction_by_invoice($kode_invoice);

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if (!$this->can_modify_transaction_items($trx)) {
            $this->session->set_flashdata('error', 'Item laundry hanya bisa dibatalkan saat transaksi masih Baru dan belum dibayar.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $detail = $this->Transaksi_model->get_detail_item($detail_id, $trx->id);

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

        $this->Transaksi_model->cancel_detail_item($detail_id, $this->session->userdata('user_id'));

        $this->sync_transaction_deadline($trx->id);
        $this->simpan_snapshot_tagihan($trx->id);

        $this->session->set_flashdata('success', 'Item laundry berhasil dibatalkan tanpa menghapus histori. Kirim ulang nota WhatsApp agar customer menerima total terbaru.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function tambah_item($kode_invoice)
    {
        $trx = $this->Transaksi_model->get_basic_transaction_by_invoice($kode_invoice);

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

        $item = $this->build_transaction_detail_item($paket, $qty, $customer_notes);

        if ((float) $item['qty'] <= 0) {
            $this->session->set_flashdata('error', 'Jumlah bawaan harus lebih dari 0.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $pesan_campuran = $this->cek_campuran_alas_kaki($this->get_active_transaction_details($trx->id), $item);
        if ($pesan_campuran !== null) {
            $this->session->set_flashdata('error', $pesan_campuran);
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->Transaksi_model->insert_detail_item([
            'id_transaksi' => $trx->id,
            'id_paket' => $paket->id_paket_laundry,
            'qty' => $item['charged_qty'],
            'harga' => $item['harga'],
            'keterangan' => $this->laundry_service_rules->build_item_keterangan($item['customer_notes']),
        ]);

        $this->sync_transaction_deadline($trx->id);
        $this->simpan_snapshot_tagihan($trx->id);

        $this->session->set_flashdata('success', 'Item laundry berhasil ditambahkan ke nota ini. Kirim ulang nota WhatsApp agar customer menerima total terbaru.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function batalkan()
    {
        $kode_invoice = $this->input->post('kode_invoice');
        $alasan_batal = trim((string) $this->input->post('alasan_batal', true));

        if ($kode_invoice === '') {
            $this->session->set_flashdata('error', 'Invoice transaksi tidak valid.');
            redirect('transaksi');
        }

        if ($alasan_batal === '') {
            $this->session->set_flashdata('error', 'Alasan batal transaksi wajib diisi.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if (strlen($alasan_batal) > 255) {
            $alasan_batal = substr($alasan_batal, 0, 255);
        }

        $trx = $this->Transaksi_model->get_basic_transaction_by_invoice($kode_invoice);

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if (!$this->can_cancel_transaction($trx)) {
            $this->session->set_flashdata('error', 'Transaksi hanya bisa dibatalkan saat status masih Baru dan belum dibayar.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $success = $this->Transaksi_model->cancel_transaction(
            $trx->id,
            $this->session->userdata('user_id'),
            $alasan_batal
        );

        if (!$success) {
            $this->session->set_flashdata('error', 'Transaksi gagal dibatalkan. Silakan muat ulang halaman ini.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->session->set_flashdata('success', 'Transaksi berhasil dibatalkan. Poin reward yang terpakai di nota ini (jika ada) sudah dikembalikan.');
        redirect('transaksi');
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

        $trx = $this->Transaksi_model->get_status_transaction_by_invoice($kode_invoice);

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if ((string) $trx->status === $this->cancelled_status) {
            $this->session->set_flashdata('error', 'Transaksi yang sudah dibatalkan tidak bisa diubah statusnya.');
            redirect('transaksi/detail/' . $kode_invoice);
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

        $this->Transaksi_model->update_status_by_invoice($kode_invoice, $data_update);
        $this->sinkron_poin_member($trx->id);

        $this->session->set_flashdata('success', 'Status Laundry berhasil diupdate menjadi: ' . strtoupper($status_baru));
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function bayar_tagihan($kode_invoice)
    {
        $id_metode_bayar = (int) $this->input->post('id_metode_bayar');
        $expected_total = $this->input->post('expected_total');

        $trx = $this->Transaksi_model->get_detail_transaction_by_invoice($kode_invoice);

        if (!$trx) {
            $this->session->set_flashdata('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }

        if ((string) $trx->status === $this->cancelled_status) {
            $this->session->set_flashdata('error', 'Transaksi yang sudah dibatalkan tidak bisa dibayar.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if ((string) $trx->dibayar === 'Sudah Dibayar') {
            $this->session->set_flashdata('error', 'Transaksi ini sudah tercatat lunas.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if (empty($id_metode_bayar)) {
            $this->session->set_flashdata('error', 'Pilih metode pembayaran terlebih dahulu.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        // Nota lama yang belum punya snapshot dikunci sekali di sini sebelum divalidasi.
        if (empty($trx->snapshot_tagihan_at)) {
            $this->simpan_snapshot_tagihan($trx->id);
            $trx = $this->Transaksi_model->get_detail_transaction_by_invoice($kode_invoice);
        }

        $ringkasan = $this->laundry_benefit_calculator->hitung($trx, $this->get_active_transaction_details($trx->id));
        $total_hitung = (int) round((float) $ringkasan['total_akhir']);
        $total_snapshot = (int) ($trx->total_akhir ?? 0);

        if ($total_hitung !== $total_snapshot) {
            $this->session->set_flashdata('error', 'Pembayaran TIDAK dicatat: total tersimpan di nota (Rp ' . number_format($total_snapshot, 0, ',', '.') . ') berbeda dengan hitungan sistem (Rp ' . number_format($total_hitung, 0, ',', '.') . '). Hubungi admin untuk pengecekan.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if ($expected_total === null || $expected_total === '' || (int) $expected_total !== $total_snapshot) {
            $this->session->set_flashdata('error', 'Pembayaran TIDAK dicatat karena total tagihan sudah berubah sejak halaman dibuka. Muat ulang halaman lalu cek total terbaru.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $data_update = array_merge([
            'dibayar' => $this->allowed_payment_statuses[1],
            'tgl_bayar' => date('Y-m-d H:i:s'),
            'id_metode_bayar' => $id_metode_bayar,
        ], $this->laundry_benefit_calculator->snapshot_fields($ringkasan));

        $payment_result = $this->Transaksi_model->record_payment($trx->id, $data_update, $total_snapshot);

        if (!$payment_result['success']) {
            $this->session->set_flashdata('error', 'Pembayaran gagal dicatat karena data transaksi sudah berubah. Silakan muat ulang halaman ini.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->sinkron_poin_member($trx->id);

        $this->session->set_flashdata('success', 'Pembayaran Rp ' . number_format($total_snapshot, 0, ',', '.') . ' berhasil dicatat. Status pengambilan tetap terpisah.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function tandai_diambil($kode_invoice)
    {
        $trx = $this->Transaksi_model->get_detail_transaction_by_invoice($kode_invoice);

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

        $this->Transaksi_model->mark_taken_by_invoice($kode_invoice, $data_update);
        $this->sinkron_poin_member($trx->id);

        $trx = $this->Transaksi_model->get_detail_transaction_by_invoice($kode_invoice);
        $details = $this->get_active_transaction_details($trx->id);

        $wa_link = '';

        if ($trx && !empty($trx->no_hp)) {
            $nama_kasir = $this->session->userdata('username');
            if (empty($nama_kasir)) {
                $nama_kasir = 'Admin';
            }

            $ringkasan = $this->laundry_benefit_calculator->ringkasan_dari_snapshot($trx, $details);
            $member = $this->get_member_settings();

            $wa_link = $this->laundry_notification_builder->build_confirmation_wa_link(
                $this->company,
                $member['batas_poin'],
                $trx->no_hp,
                $trx->kode_invoice,
                $trx->nama_pelanggan,
                $trx->tgl_masuk,
                $trx->batas_waktu,
                $details,
                $ringkasan['total_normal'],
                $this->build_wa_meta($trx, $ringkasan, $details, 'pengambilan', [
                    'nama_kasir' => $nama_kasir,
                    'show_potential_poin' => false,
                ])
            );
        }

        $this->session->set_flashdata('wa_link', $wa_link);
        $this->session->set_flashdata('success', 'Cucian berhasil ditandai sudah diambil.');

        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function cetak($kode_invoice)
    {
        $data['transaksi'] = $this->Transaksi_model->get_print_transaction_by_invoice($kode_invoice);

        if (!$data['transaksi']) {
            redirect('transaksi');
        }

        $data['detail'] = $this->get_active_transaction_details($data['transaksi']->id);
        // Cetak hanya membaca; snapshot tidak ditulis di sini.
        $data['ringkasan_pembayaran'] = $this->hitung_ringkasan_transaksi($data['transaksi'], $data['detail']);

        $data['company'] = $this->company;
        $this->load->view('transaksi/cetak', $data);
    }
}
