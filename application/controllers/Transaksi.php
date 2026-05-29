<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi extends MY_Controller
{
    private $allowed_statuses = ['Baru', 'Proses', 'Selesai', 'Diambil'];
    private $allowed_payment_statuses = ['Belum Dibayar', 'Sudah Dibayar'];
    private $promo_config_key = 'promo';
    private $promo_free_kg = 3;
    private $batas_poin_member = 8;
    private $reward_member_gratis_qty = 3;
    private $promo_gratis_default_min_kg = 7;
    private $promo_gratis_default_qty = 1;

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

    private function get_promo_gratis_settings()
    {
        $is_enabled = isset($this->company['promo_gratis_aktif']) && (string) $this->company['promo_gratis_aktif'] === '1';
        $min_kg = isset($this->company['promo_gratis_min_kg']) ? (float) $this->company['promo_gratis_min_kg'] : $this->promo_gratis_default_min_kg;
        $gratis_qty = isset($this->company['promo_gratis_qty']) ? (float) $this->company['promo_gratis_qty'] : $this->promo_gratis_default_qty;
        $label = trim((string) ($this->company['promo_gratis_label'] ?? 'Promo Minimal 7 Kg Gratis 1 Kg'));

        if ($min_kg <= 0) {
            $min_kg = (float) $this->promo_gratis_default_min_kg;
        }

        if ($gratis_qty <= 0) {
            $gratis_qty = (float) $this->promo_gratis_default_qty;
        }

        if ($label === '') {
            $label = 'Promo Minimal 7 Kg Gratis 1 Kg';
        }

        return [
            'is_enabled' => $is_enabled,
            'min_kg' => $min_kg,
            'gratis_qty' => $gratis_qty,
            'label' => $label,
        ];
    }

    private function get_member_settings()
    {
        return [
            'batas_poin' => $this->batas_poin_member,
            'gratis_qty' => $this->reward_member_gratis_qty,
            'label_reward' => 'Reward Member Gratis Cuci 3 Kg Reguler / Satu Hari',
        ];
    }

    private function format_qty($qty)
    {
        return rtrim(rtrim(number_format((float) $qty, 2, '.', ''), '0'), '.');
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

    private function is_layanan_cuci_komplit($nama_tipe, $nama_paket = '')
    {
        $service_name = strtolower(trim((string) $nama_tipe . ' ' . $nama_paket));
        $service_name = preg_replace('/\s+/', ' ', $service_name);

        return strpos($service_name, 'cuci komplit') !== false;
    }

    private function is_layanan_reward_member($nama_tipe, $nama_paket = '')
    {
        if (!$this->is_layanan_cuci_komplit($nama_tipe, $nama_paket)) {
            return false;
        }

        $paket = strtolower(trim((string) $nama_paket));
        $paket = preg_replace('/\s+/', ' ', $paket);

        if ($paket === '' || strpos($paket, 'express') !== false) {
            return false;
        }

        return strpos($paket, 'reguler') !== false || strpos($paket, 'satu hari') !== false;
    }

    private function is_reward_member_service_eligible($nama_satuan, $nama_tipe, $nama_paket = '')
    {
        return $this->is_kg_unit($nama_satuan) && $this->is_layanan_reward_member($nama_tipe, $nama_paket);
    }

    private function is_promo_gratis_service_eligible($nama_satuan, $nama_tipe, $nama_paket = '')
    {
        return $this->is_kg_unit($nama_satuan) && $this->is_layanan_cuci_komplit($nama_tipe, $nama_paket);
    }

    private function build_promo_gratis_keterangan($promo_preview)
    {
        $label = trim((string) ($promo_preview['promo_gratis_label'] ?? 'Promo Gratis'));
        $total_kg = $this->format_qty((float) ($promo_preview['total_kg_cuci_komplit'] ?? 0));
        $gratis_qty = $this->format_qty((float) ($promo_preview['promo_gratis_qty'] ?? 0));

        if ($label === '') {
            $label = 'Promo Gratis';
        }

        return $label . ' | Berlaku kelipatan | Total Cuci Komplit ' . $total_kg . ' kg | Gratis ' . $gratis_qty . ' kg';
    }

    private function get_pelanggan_member_data($id_pelanggan)
    {
        $id_pelanggan = (int) $id_pelanggan;
        if ($id_pelanggan <= 0) {
            return null;
        }

        return $this->db
            ->select('id, nama, aktif, poin_member')
            ->where('id', $id_pelanggan)
            ->get('m_pelanggan')
            ->row();
    }

    private function get_poin_member_pelanggan($id_pelanggan)
    {
        $pelanggan = $this->get_pelanggan_member_data($id_pelanggan);
        if (!$pelanggan) {
            return 0;
        }

        return max(0, min($this->batas_poin_member, (int) ($pelanggan->poin_member ?? 0)));
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
            $notes[] = 'Promo 3 Kg gratis, dibayar ' . $this->format_qty($charged_qty) . ' ' . strtoupper((string) $unit_name);
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

    private function transaksi_memiliki_layanan_cuci_komplit($id_transaksi)
    {
        $details = $this->get_active_transaction_details((int) $id_transaksi);

        foreach ($details as $detail) {
            if ($this->is_layanan_cuci_komplit($detail->nama_tipe ?? '', $detail->nama_paket ?? '')) {
                return true;
            }
        }

        return false;
    }

    private function get_detail_benefit_flags($details)
    {
        $flags = [
            'punya_cuci_komplit' => false,
            'punya_layanan_reward' => false,
        ];

        foreach ((array) $details as $detail) {
            $nama_tipe = (string) ($detail->nama_tipe ?? $detail['nama_tipe'] ?? '');
            $nama_paket = (string) ($detail->nama_paket ?? $detail['nama_paket'] ?? '');
            $nama_satuan = (string) ($detail->nama_satuan ?? $detail['nama_satuan'] ?? '');

            if ($this->is_layanan_cuci_komplit($nama_tipe, $nama_paket)) {
                $flags['punya_cuci_komplit'] = true;
            }

            if ($this->is_reward_member_service_eligible($nama_satuan, $nama_tipe, $nama_paket)) {
                $flags['punya_layanan_reward'] = true;
            }

            if ($flags['punya_cuci_komplit'] && $flags['punya_layanan_reward']) {
                break;
            }
        }

        return $flags;
    }

    private function hitung_total_akhir_wa($total_tagihan, $reward_potongan = 0, $promo_gratis_potongan = 0)
    {
        return max(0, (float) $total_tagihan - (int) $reward_potongan - (int) $promo_gratis_potongan);
    }

    private function build_benefit_customer_message_lines($meta = [])
    {
        $lines = [];
        $poin_pelanggan = max(0, min($this->batas_poin_member, (int) ($meta['poin_member_pelanggan'] ?? 0)));
        $reward_dipakai = !empty($meta['reward_member_dipakai']) && (float) ($meta['reward_gratis_qty'] ?? 0) > 0;
        $promo_dipakai = !empty($meta['promo_gratis_dipakai']) && (float) ($meta['promo_gratis_qty'] ?? 0) > 0;
        $poin_sudah_masuk = !empty($meta['poin_diberikan_pada']);
        $punya_cuci_komplit = !empty($meta['punya_cuci_komplit']);
        $punya_layanan_reward = !empty($meta['punya_layanan_reward']);
        $show_potential_poin = !empty($meta['show_potential_poin']);

        if ($reward_dipakai) {
            $lines[] = 'Reward Member digunakan';
            $lines[] = 'Poin member telah digunakan untuk reward';
            $lines[] = 'Gratis Reward: ' . $this->format_qty((float) ($meta['reward_gratis_qty'] ?? 0)) . ' kg';
            $lines[] = 'Potongan Reward: Rp ' . number_format((int) ($meta['reward_potongan'] ?? 0), 0, ',', '.');
            $lines[] = 'Total poin sekarang: 0';

            return $lines;
        }

        if ($promo_dipakai) {
            $promo_label = trim((string) ($meta['promo_gratis_keterangan'] ?? ''));
            if ($promo_label === '') {
                $promo_label = 'Promo Gratis Umum';
            }

            $lines[] = 'Promo Gratis: ' . rawurlencode($promo_label);
            $lines[] = 'Gratis Promo: ' . $this->format_qty((float) ($meta['promo_gratis_qty'] ?? 0)) . ' kg';
            $lines[] = 'Potongan Promo: Rp ' . number_format((int) ($meta['promo_gratis_potongan'] ?? 0), 0, ',', '.');
        }

        if ($poin_sudah_masuk) {
            $lines[] = 'Poin didapat: +1';
            $lines[] = 'Total poin sekarang: ' . $poin_pelanggan;

            return $lines;
        }

        if ($poin_pelanggan >= $this->batas_poin_member) {
            $lines[] = 'Poin member saat ini: ' . $poin_pelanggan;
            $lines[] = 'Reward member tersimpan untuk Cuci Komplit Reguler atau Satu Hari.';

            return $lines;
        }

        if ($show_potential_poin && $punya_cuci_komplit) {
            $lines[] = 'Poin saat ini: ' . $poin_pelanggan;

            if ($poin_pelanggan === ($this->batas_poin_member - 1)) {
                $lines[] = 'Setelah cucian selesai dan lunas, poin menjadi 8 dan bisa digunakan untuk reward member.';
            } else {
                $lines[] = 'Poin akan otomatis bertambah setelah cucian selesai dan lunas.';
            }
        }

        return $lines;
    }

    private function sinkron_poin_member($id_transaksi)
    {
        $this->db->select('id, id_pelanggan, status, dibayar, poin_diberikan_pada, reward_member_dipakai');
        $this->db->from('transaksi');
        $this->db->where('id', (int) $id_transaksi);
        $trx = $this->db->get()->row();

        if (
            !$trx
            || (int) ($trx->id_pelanggan ?? 0) <= 0
            || !in_array((string) ($trx->status ?? ''), ['Selesai', 'Diambil'], true)
            || (string) ($trx->dibayar ?? '') !== 'Sudah Dibayar'
            || !empty($trx->poin_diberikan_pada)
            || !empty($trx->reward_member_dipakai)
            || !$this->transaksi_memiliki_layanan_cuci_komplit($trx->id)
        ) {
            return false;
        }

        $this->db->trans_start();

        $this->db->set('poin_diberikan_pada', date('Y-m-d H:i:s'));
        $this->db->where('id', (int) $trx->id);
        $this->db->where('poin_diberikan_pada IS NULL', null, false);
        $this->db->where_in('status', ['Selesai', 'Diambil']);
        $this->db->where('dibayar', 'Sudah Dibayar');
        $this->db->where('COALESCE(reward_member_dipakai, 0) = 0', null, false);
        $this->db->update('transaksi');

        $poin_berhasil_dikunci = $this->db->affected_rows() > 0;

        if ($poin_berhasil_dikunci) {
            $this->db->set('poin_member', 'LEAST(COALESCE(poin_member, 0) + 1, ' . (int) $this->batas_poin_member . ')', false);
            $this->db->where('id', (int) $trx->id_pelanggan);
            $this->db->update('m_pelanggan');
        }

        $this->db->trans_complete();

        return $poin_berhasil_dikunci && $this->db->trans_status();
    }

    private function hitung_reward_member_pembayaran($id_transaksi)
    {
        $result = [
            'reward_tersedia' => false,
            'reward_gratis_qty' => 0,
            'reward_potongan' => 0,
            'total_sebelum_reward' => 0,
            'total_setelah_reward' => 0,
            'detail_cuci_komplit' => 0,
            'id_pelanggan' => 0,
        ];

        $this->db->select('id, id_pelanggan, dibayar, reward_member_dipakai');
        $this->db->from('transaksi');
        $this->db->where('id', (int) $id_transaksi);
        $trx = $this->db->get()->row();

        if (!$trx || (int) ($trx->id_pelanggan ?? 0) <= 0) {
            return $result;
        }

        $result['id_pelanggan'] = (int) $trx->id_pelanggan;
        $detail_rows = $this->get_active_transaction_details($trx->id);

        foreach ($detail_rows as $detail) {
            $result['total_sebelum_reward'] += (float) ($detail->subtotal ?? 0);
        }

        $result['total_setelah_reward'] = $result['total_sebelum_reward'];

        if (
            (string) ($trx->dibayar ?? '') !== 'Belum Dibayar'
            || !empty($trx->reward_member_dipakai)
            || $this->get_poin_member_pelanggan($trx->id_pelanggan) < $this->batas_poin_member
        ) {
            return $result;
        }

        $sisa_reward_qty = (float) $this->reward_member_gratis_qty;

        foreach ($detail_rows as $detail) {
            if (
                $sisa_reward_qty <= 0
                || !$this->is_reward_member_service_eligible($detail->nama_satuan ?? '', $detail->nama_tipe ?? '', $detail->nama_paket ?? '')
            ) {
                continue;
            }

            $qty_valid = max(0, (float) ($detail->qty ?? 0));
            if ($qty_valid <= 0) {
                continue;
            }

            $gratis_qty = min($sisa_reward_qty, $qty_valid);
            $result['detail_cuci_komplit']++;
            $result['reward_gratis_qty'] += $gratis_qty;
            $result['reward_potongan'] += (int) round((float) ($detail->harga ?? 0) * $gratis_qty);
            $sisa_reward_qty -= $gratis_qty;
        }

        if ($result['reward_gratis_qty'] <= 0) {
            return $result;
        }

        $result['reward_tersedia'] = true;
        $result['total_setelah_reward'] = max(0, $result['total_sebelum_reward'] - $result['reward_potongan']);

        return $result;
    }

    private function hitung_promo_gratis_pembayaran($id_transaksi)
    {
        $settings = $this->get_promo_gratis_settings();
        $result = [
            'promo_tersedia' => false,
            'promo_gratis_qty' => 0,
            'promo_gratis_potongan' => 0,
            'promo_gratis_label' => $settings['label'],
            'promo_gratis_keterangan' => '',
            'total_kg_cuci_komplit' => 0,
            'total_sebelum_promo' => 0,
            'total_setelah_promo' => 0,
            'id_transaksi_detail_dasar' => 0,
            'harga_dasar' => 0,
            'jumlah_kelipatan' => 0,
        ];

        $this->db->select('id, dibayar, reward_member_dipakai, promo_gratis_dipakai');
        $this->db->from('transaksi');
        $this->db->where('id', (int) $id_transaksi);
        $trx = $this->db->get()->row();

        if (!$trx) {
            return $result;
        }

        $detail_rows = $this->get_active_transaction_details($trx->id);
        foreach ($detail_rows as $detail) {
            $result['total_sebelum_promo'] += (float) ($detail->subtotal ?? 0);
        }

        $result['total_setelah_promo'] = $result['total_sebelum_promo'];

        if (
            !$settings['is_enabled']
            || (string) ($trx->dibayar ?? '') !== 'Belum Dibayar'
            || !empty($trx->reward_member_dipakai)
            || !empty($trx->promo_gratis_dipakai)
        ) {
            return $result;
        }

        $detail_eligible = [];

        foreach ($detail_rows as $detail) {
            if (!$this->is_promo_gratis_service_eligible($detail->nama_satuan ?? '', $detail->nama_tipe ?? '', $detail->nama_paket ?? '')) {
                continue;
            }

            $qty_valid = max(0, (float) ($detail->qty ?? 0));
            if ($qty_valid <= 0) {
                continue;
            }

            $result['total_kg_cuci_komplit'] += $qty_valid;
            $detail_eligible[] = $detail;
        }

        if ($result['total_kg_cuci_komplit'] < (float) $settings['min_kg'] || empty($detail_eligible)) {
            return $result;
        }

        $jumlah_kelipatan = (int) floor((((float) $result['total_kg_cuci_komplit']) + 0.0000001) / (float) $settings['min_kg']);
        if ($jumlah_kelipatan <= 0) {
            return $result;
        }

        $gratis_qty = min(
            (float) $result['total_kg_cuci_komplit'],
            (float) $jumlah_kelipatan * (float) $settings['gratis_qty']
        );

        if ($gratis_qty <= 0) {
            return $result;
        }

        usort($detail_eligible, function ($a, $b) {
            $harga_a = (float) ($a->harga ?? 0);
            $harga_b = (float) ($b->harga ?? 0);

            if ($harga_a === $harga_b) {
                return (int) ($a->id ?? 0) <=> (int) ($b->id ?? 0);
            }

            return $harga_a <=> $harga_b;
        });

        $sisa_gratis_qty = $gratis_qty;
        $promo_potongan = 0.0;
        $detail_dasar = null;

        foreach ($detail_eligible as $detail) {
            if ($sisa_gratis_qty <= 0) {
                break;
            }

            $qty_valid = max(0, (float) ($detail->qty ?? 0));
            if ($qty_valid <= 0) {
                continue;
            }

            $qty_promo_detail = min($sisa_gratis_qty, $qty_valid);
            if ($qty_promo_detail <= 0) {
                continue;
            }

            if ($detail_dasar === null) {
                $detail_dasar = $detail;
            }

            $promo_potongan += $qty_promo_detail * (float) ($detail->harga ?? 0);
            $sisa_gratis_qty -= $qty_promo_detail;
        }

        $result['promo_tersedia'] = true;
        $result['promo_gratis_qty'] = $gratis_qty;
        $result['promo_gratis_potongan'] = (int) round($promo_potongan);
        $result['id_transaksi_detail_dasar'] = (int) ($detail_dasar->id ?? 0);
        $result['harga_dasar'] = (int) round((float) ($detail_dasar->harga ?? 0));
        $result['jumlah_kelipatan'] = $jumlah_kelipatan;
        $result['promo_gratis_keterangan'] = $this->build_promo_gratis_keterangan($result);
        $result['total_setelah_promo'] = max(0, $result['total_sebelum_promo'] - $result['promo_gratis_potongan']);

        return $result;
    }

    private function siapkan_ringkasan_pembayaran($transaksi, $total_normal, $reward_preview = null, $izinkan_preview = true, $promo_preview = null)
    {
        $ringkasan = [
            'total_normal' => (float) $total_normal,
            'total_akhir' => (float) $total_normal,
            'reward_tersedia' => false,
            'reward_preview' => false,
            'reward_dipakai' => false,
            'reward_gratis_qty' => 0,
            'reward_potongan' => 0,
            'promo_tersedia' => false,
            'promo_preview' => false,
            'promo_dipakai' => false,
            'promo_gratis_qty' => 0,
            'promo_gratis_potongan' => 0,
            'promo_gratis_label' => '',
            'promo_gratis_keterangan' => '',
            'promo_total_kg_cuci_komplit' => 0,
        ];

        if (!$transaksi) {
            return $ringkasan;
        }

        $reward_gratis_terkunci = (float) ($transaksi->reward_gratis_qty ?? 0);
        $reward_potongan_terkunci = (int) ($transaksi->reward_potongan ?? 0);

        if (!empty($transaksi->reward_member_dipakai) && $reward_gratis_terkunci > 0) {
            $ringkasan['reward_tersedia'] = true;
            $ringkasan['reward_dipakai'] = true;
            $ringkasan['reward_gratis_qty'] = $reward_gratis_terkunci;
            $ringkasan['reward_potongan'] = $reward_potongan_terkunci;
            $ringkasan['total_akhir'] = max(0, $ringkasan['total_normal'] - $reward_potongan_terkunci);

            return $ringkasan;
        }

        $promo_gratis_terkunci = (float) ($transaksi->promo_gratis_qty ?? 0);
        $promo_gratis_potongan_terkunci = (int) ($transaksi->promo_gratis_potongan ?? 0);

        if (!empty($transaksi->promo_gratis_dipakai) && $promo_gratis_terkunci > 0) {
            $total_kg_cuci_komplit = 0;
            foreach ($this->get_active_transaction_details($transaksi->id) as $detail_row) {
                if ($this->is_promo_gratis_service_eligible($detail_row->nama_satuan ?? '', $detail_row->nama_tipe ?? '', $detail_row->nama_paket ?? '')) {
                    $total_kg_cuci_komplit += max(0, (float) ($detail_row->qty ?? 0));
                }
            }

            $ringkasan['promo_tersedia'] = true;
            $ringkasan['promo_dipakai'] = true;
            $ringkasan['promo_gratis_qty'] = $promo_gratis_terkunci;
            $ringkasan['promo_gratis_potongan'] = $promo_gratis_potongan_terkunci;
            $ringkasan['promo_gratis_keterangan'] = trim((string) ($transaksi->promo_gratis_keterangan ?? ''));
            $ringkasan['promo_gratis_label'] = $ringkasan['promo_gratis_keterangan'] !== '' ? $ringkasan['promo_gratis_keterangan'] : ($this->get_promo_gratis_settings()['label'] ?? 'Promo Gratis');
            $ringkasan['promo_total_kg_cuci_komplit'] = (float) $total_kg_cuci_komplit;
            $ringkasan['total_akhir'] = max(0, $ringkasan['total_normal'] - $promo_gratis_potongan_terkunci);

            return $ringkasan;
        }

        if (!$izinkan_preview || (string) ($transaksi->dibayar ?? '') !== 'Belum Dibayar') {
            return $ringkasan;
        }

        if (!is_array($reward_preview)) {
            $reward_preview = $this->hitung_reward_member_pembayaran($transaksi->id);
        }

        if (!empty($reward_preview['reward_tersedia'])) {
            $ringkasan['reward_tersedia'] = true;
            $ringkasan['reward_preview'] = true;
            $ringkasan['reward_gratis_qty'] = (float) ($reward_preview['reward_gratis_qty'] ?? 0);
            $ringkasan['reward_potongan'] = (int) ($reward_preview['reward_potongan'] ?? 0);
            $ringkasan['total_akhir'] = (float) ($reward_preview['total_setelah_reward'] ?? $ringkasan['total_normal']);

            return $ringkasan;
        }

        if (!is_array($promo_preview)) {
            $promo_preview = $this->hitung_promo_gratis_pembayaran($transaksi->id);
        }

        if (!empty($promo_preview['promo_tersedia'])) {
            $ringkasan['promo_tersedia'] = true;
            $ringkasan['promo_preview'] = true;
            $ringkasan['promo_gratis_qty'] = (float) ($promo_preview['promo_gratis_qty'] ?? 0);
            $ringkasan['promo_gratis_potongan'] = (int) ($promo_preview['promo_gratis_potongan'] ?? 0);
            $ringkasan['promo_gratis_label'] = trim((string) ($promo_preview['promo_gratis_label'] ?? ''));
            $ringkasan['promo_gratis_keterangan'] = trim((string) ($promo_preview['promo_gratis_keterangan'] ?? ''));
            $ringkasan['promo_total_kg_cuci_komplit'] = (float) ($promo_preview['total_kg_cuci_komplit'] ?? 0);
            $ringkasan['total_akhir'] = (float) ($promo_preview['total_setelah_promo'] ?? $ringkasan['total_normal']);
        }

        return $ringkasan;
    }

    private function siapkan_badge_benefit_transaksi($transaksi, $active_detail = [], $reward_preview = null, $promo_preview = null)
    {
        if (!$transaksi) {
            return null;
        }

        $poin_pelanggan = max(0, min($this->batas_poin_member, (int) ($transaksi->poin_member_pelanggan ?? 0)));
        $reward_tersedia = !empty($reward_preview['reward_tersedia']);
        $promo_tersedia = !empty($promo_preview['promo_tersedia']);
        $reward_dipakai = !empty($transaksi->reward_member_dipakai);
        $promo_dipakai = !empty($transaksi->promo_gratis_dipakai);
        $memiliki_cuci_komplit = false;
        $memiliki_layanan_reward = false;

        foreach ((array) $active_detail as $detail_row) {
            if ($this->is_layanan_cuci_komplit($detail_row->nama_tipe ?? '', $detail_row->nama_paket ?? '')) {
                $memiliki_cuci_komplit = true;
            }

            if ($this->is_reward_member_service_eligible($detail_row->nama_satuan ?? '', $detail_row->nama_tipe ?? '', $detail_row->nama_paket ?? '')) {
                $memiliki_layanan_reward = true;
            }
        }

        if ($reward_dipakai) {
            return [
                'label' => 'Reward Member',
                'icon' => 'fas fa-gift',
                'class' => 'trx-benefit-badge-success',
            ];
        }

        if ((string) ($transaksi->dibayar ?? '') === 'Belum Dibayar' && $reward_tersedia) {
            return [
                'label' => 'Reward Member',
                'icon' => 'fas fa-gift',
                'class' => 'trx-benefit-badge-success',
            ];
        }

        if ($promo_dipakai) {
            return [
                'label' => 'Promo Gratis',
                'icon' => 'fas fa-tags',
                'class' => 'trx-benefit-badge-warning',
            ];
        }

        if ((string) ($transaksi->dibayar ?? '') === 'Belum Dibayar' && $promo_tersedia) {
            return [
                'label' => 'Promo Gratis Tersedia',
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

        if (
            $poin_pelanggan >= $this->batas_poin_member
            && !$memiliki_layanan_reward
            && !$reward_dipakai
            && !$promo_dipakai
            && !$reward_tersedia
            && !$promo_tersedia
        ) {
            return [
                'label' => 'Poin 8 Tersimpan',
                'icon' => 'fas fa-coins',
                'class' => 'trx-benefit-badge-stored',
            ];
        }

        if (
            $memiliki_cuci_komplit
            && empty($transaksi->poin_diberikan_pada)
            && !$reward_dipakai
            && !$promo_dipakai
            && !$reward_tersedia
            && !$promo_tersedia
        ) {
            return [
                'label' => 'Dapat Poin',
                'icon' => 'fas fa-star',
                'class' => 'trx-benefit-badge-primary',
            ];
        }

        return null;
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

    private function build_confirmation_message($invoice, $pelanggan_nama, $tgl_terima, $tgl_selesai, $details, $total_tagihan, $payment_meta = [])
    {
        $list_item_wa = '';

        foreach ($details as $item) {
            $nama_tipe = strtoupper(trim((string) ($item->nama_tipe ?? $item['nama_tipe'] ?? '')));
            $nama_paket = trim((string) ($item->nama_paket ?? $item['nama_paket'] ?? ''));
            $harga_satuan = (float) ($item->harga ?? $item['harga'] ?? 0);
            $satuan_wa = trim((string) ($item->nama_satuan ?? $item['nama_satuan'] ?? ''));
            $promo_applied = !empty($item->promo_applied ?? $item['promo_applied'] ?? false);
            $charged_qty = (float) ($item->charged_qty ?? $item['charged_qty'] ?? $item->qty ?? $item['qty'] ?? 0);
            $customer_notes = trim((string) ($item->customer_notes ?? $item['customer_notes'] ?? ''));
            $item_note_text = trim((string) ($item->item_note_text ?? $item['item_note_text'] ?? ''));
            if ($item_note_text === '') {
                $item_note_text = $this->build_item_note_text(
                    $promo_applied,
                    $charged_qty,
                    $satuan_wa !== '' ? $satuan_wa : 'Kg/Pcs',
                    $customer_notes
                );
            }
            $qty_for_message = $item->qty_label ?? $item['qty_label'] ?? $item->qty ?? $item['qty'] ?? 0;
            $subtotal_item = (float) ($item->subtotal ?? $item['subtotal'] ?? ($harga_satuan * $qty_for_message));

            $list_item_wa .= '- ' . $nama_tipe . ' / ' . strtoupper($nama_paket) . ', ' . $qty_for_message . ' ' . strtoupper($satuan_wa) . "%0A";
            $list_item_wa .= '@ Rp' . number_format($harga_satuan, 0, ',', '.') . ', Total Rp' . number_format($subtotal_item, 0, ',', '.') . "%0A";
            $list_item_wa .= 'Ket : ' . rawurlencode($item_note_text) . "%0A";
        }

        $tgl_terima_fmt = date('d/m/Y H:i', strtotime($tgl_terima));
        $tgl_selesai_fmt = !empty($tgl_selesai) ? date('d/m/Y H:i', strtotime($tgl_selesai)) : '-';
        $mode = strtolower(trim((string) ($payment_meta['mode'] ?? 'detail')));
        if (!in_array($mode, ['awal', 'detail', 'pengambilan'], true)) {
            $mode = 'detail';
        }
        $reward_dipakai = !empty($payment_meta['reward_member_dipakai']) && (float) ($payment_meta['reward_gratis_qty'] ?? 0) > 0;
        $reward_gratis_qty = (float) ($payment_meta['reward_gratis_qty'] ?? 0);
        $reward_potongan = (int) ($payment_meta['reward_potongan'] ?? 0);
        $promo_dipakai = !empty($payment_meta['promo_gratis_dipakai']) && (float) ($payment_meta['promo_gratis_qty'] ?? 0) > 0;
        $promo_gratis_qty = (float) ($payment_meta['promo_gratis_qty'] ?? 0);
        $promo_gratis_potongan = (int) ($payment_meta['promo_gratis_potongan'] ?? 0);
        $promo_gratis_keterangan = trim((string) ($payment_meta['promo_gratis_keterangan'] ?? ''));
        $total_akhir = $this->hitung_total_akhir_wa($total_tagihan, $reward_potongan, $promo_gratis_potongan);
        $total_fmt = number_format((float) $total_tagihan, 0, ',', '.');
        $total_akhir_fmt = number_format((float) $total_akhir, 0, ',', '.');
        $is_paid = (string) ($payment_meta['dibayar'] ?? '') === 'Sudah Dibayar';
        $paid_amount_fmt = number_format($is_paid ? (float) $total_akhir : 0, 0, ',', '.');
        $remaining_amount_fmt = number_format($is_paid ? 0 : (float) $total_akhir, 0, ',', '.');
        $payment_method = trim((string) ($payment_meta['nama_metode_bayar'] ?? ''));
        $paid_at = !empty($payment_meta['tgl_bayar']) ? date('d/m/Y H:i', strtotime($payment_meta['tgl_bayar'])) : '';
        $diambil_at = !empty($payment_meta['tgl_diambil']) ? date('d/m/Y H:i', strtotime($payment_meta['tgl_diambil'])) : '';
        $nama_kasir = trim((string) ($payment_meta['nama_kasir'] ?? 'Admin'));
        $benefit_lines = $this->build_benefit_customer_message_lines([
            'reward_member_dipakai' => $reward_dipakai,
            'reward_gratis_qty' => $reward_gratis_qty,
            'reward_potongan' => $reward_potongan,
            'promo_gratis_dipakai' => $promo_dipakai,
            'promo_gratis_qty' => $promo_gratis_qty,
            'promo_gratis_potongan' => $promo_gratis_potongan,
            'promo_gratis_keterangan' => $promo_gratis_keterangan,
            'poin_member_pelanggan' => $payment_meta['poin_member_pelanggan'] ?? 0,
            'poin_diberikan_pada' => $payment_meta['poin_diberikan_pada'] ?? null,
            'punya_cuci_komplit' => !empty($payment_meta['punya_cuci_komplit']),
            'punya_layanan_reward' => !empty($payment_meta['punya_layanan_reward']),
            'show_potential_poin' => !empty($payment_meta['show_potential_poin']),
        ]);

        $company_name = $this->company['company_name'] ?? 'APP Laundry';
        $company_address = $this->company['company_address'] ?? 'Jalan';
        $company_phone = $this->company['company_phone'] ?? '08000000000';

        $pesan = ($mode === 'pengambilan' ? 'FAKTUR BUKTI PENGAMBILAN' : 'FAKTUR ELEKTRONIK TRANSAKSI REGULER') . "%0A";
        $pesan .= "{$company_name}%0A";
        $pesan .= "{$company_address}%0A";
        $pesan .= "{$company_phone}%0A%0A";
        $pesan .= "Nomor Nota :%0A";
        $pesan .= "$invoice%0A%0A";
        $pesan .= "Pelanggan Yth :%0A";
        $pesan .= "$pelanggan_nama%0A%0A";
        $pesan .= "Terima : $tgl_terima_fmt%0A";
        if ($mode === 'pengambilan') {
            if ($diambil_at !== '') {
                $pesan .= "Diambil : $diambil_at%0A";
            }
        } else {
            $pesan .= "Selesai : $tgl_selesai_fmt%0A";
        }
        $pesan .= "%0A======================%0A";
        $pesan .= ($mode === 'pengambilan' ? 'Detail pengambilan:' : 'Detail pesanan:') . "%0A";
        $pesan .= "Layanan:%0A";
        $pesan .= $list_item_wa;
        $pesan .= "%0A==============%0A";
        $pesan .= "Detail biaya :%0A";
        $pesan .= "Total tagihan : Rp$total_fmt%0A";
        $pesan .= "Grand total : Rp$total_akhir_fmt%0A%0A";

        if (!empty($benefit_lines)) {
            $pesan .= "*Benefit Customer*%0A";
            foreach ($benefit_lines as $line) {
                $pesan .= $line . "%0A";
            }
            $pesan .= "%0A";
        }

        $pesan .= "Pembayaran:%0A";
        if ($is_paid) {
            $pesan .= "Dibayar : Rp$paid_amount_fmt%0A";
            if ($payment_method !== '') {
                $pesan .= "Metode : $payment_method%0A";
            }
            if ($paid_at !== '') {
                $pesan .= "Tanggal bayar : $paid_at%0A";
            }
            $pesan .= "Sisa tagihan : Rp$remaining_amount_fmt%0A";
            $pesan .= "Status: Lunas%0A%0A";
        } else {
            $pesan .= "Sisa tagihan : Rp$remaining_amount_fmt%0A";
            $pesan .= "Status: Belum lunas%0A%0A";
        }

        if ($mode === 'pengambilan') {
            if ($diambil_at !== '') {
                $pesan .= "Pengambilan:%0A";
                $pesan .= "Diserahkan pada : $diambil_at%0A";
                $pesan .= "Oleh : $nama_kasir%0A";
                $pesan .= "Status Laundry : Sudah Diambil%0A%0A";
            }

            $pesan .= "=================%0A";
            $pesan .= "Kami telah menyerahkan barang dan diterima dengan kondisi baik%0A";
            $pesan .= "Terima kasih";

            return $pesan;
        }

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

    private function build_confirmation_wa_link($phone_number, $invoice, $pelanggan_nama, $tgl_terima, $tgl_selesai, $details, $total_tagihan, $payment_meta = [])
    {
        $nomor = $this->normalize_whatsapp_number($phone_number);

        if ($nomor === '') {
            return '';
        }

        $pesan = $this->build_confirmation_message($invoice, $pelanggan_nama, $tgl_terima, $tgl_selesai, $details, $total_tagihan, $payment_meta);

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
            $detail->qty_label = $this->format_qty($detail->qty);
            $detail->subtotal = (float) $detail->harga * (float) $detail->qty;
            $detail->customer_notes = $this->extract_customer_notes($detail->keterangan ?? '');
            $detail->item_note_text = '-';

            if ($promo) {
                $detail->actual_qty = (float) ($promo['actual_qty'] ?? $detail->qty);
                $detail->rounded_qty = (float) ($promo['rounded_qty'] ?? $detail->actual_qty);
                $detail->charged_qty = (float) $detail->qty;
                $detail->promo_free_qty = (float) ($promo['free_qty'] ?? 0);
                $detail->promo_applied = !empty($promo['promo_type']);
                $detail->promo_label = $promo['promo_label'] ?? 'Promo Gratis Cuci 3 Kg';
                $detail->qty_label = $this->format_qty($detail->actual_qty);
                $detail->subtotal = (float) $detail->harga * (float) $detail->qty;
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
        $data['member_settings'] = $this->get_member_settings();
        $this->db->where('aktif', 1);
        $this->db->order_by('nama', 'ASC');
        $data['pelanggan'] = $this->db->get('m_pelanggan')->result();
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
        $promo_requested = $this->input->post('promo_cuci_3kg') == '1';
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

        $this->db->select('m_paket_laundry.*, m_satuan.nama_satuan, m_kategori.nama_kategori, m_tipe.nama_tipe');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        $this->db->where('id_paket_laundry', $id_paket);
        $paket = $this->db->get()->row();

        if ($paket) {
            $item = $this->build_transaction_detail_item($paket, $qty, $promo_requested, $customer_notes);

            if (!$this->session->userdata('cart')) {
                $cart = [];
            } else {
                $cart = $this->session->userdata('cart');
            }

            $cart_key = $id_paket . '-' . (!empty($item['promo_applied']) ? 'promo' : 'normal');

            if (isset($cart[$cart_key])) {
                $cart[$cart_key]['qty'] += (float) $item['qty'];
                $cart[$cart_key] = $this->normalize_cart_item($cart[$cart_key]);
            } else {
                $cart[$cart_key] = $item;
            }

            $this->session->set_userdata('cart', $cart);
            $this->set_cart_customer_id($id_pelanggan);
            $this->get_normalized_cart(true);

            echo json_encode([
                'status' => 'success',
                'promo_applied' => !empty($item['promo_applied']),
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
                $qty_label = $this->format_qty($item['qty']);
                $harga_label = 'Rp ' . number_format($item['harga'], 0, ',', '.');
                $promo_html = '';

                if (!empty($item['promo_applied'])) {
                    $promo_html = '<small class="d-block text-primary mt-1"><i class="fas fa-tags me-1"></i>' . $item['promo_label'] . ' aktif: berat asli ' . $qty_label . ' ' . $item['nama_satuan'] . ', dibulatkan ' . $this->format_qty($item['rounded_qty']) . ' ' . $item['nama_satuan'] . ', dibayar ' . $this->format_qty($item['charged_qty']) . ' ' . $item['nama_satuan'] . '.</small>';
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

        $this->db->trans_start();
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

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            $this->session->set_flashdata('error', 'Transaksi gagal disimpan. Silakan coba lagi.');
            redirect('transaksi/baru');
        }

        $pelanggan = $this->db->get_where('m_pelanggan', ['id' => $id_pelanggan])->row();
        $wa_link = "";

        if ($pelanggan && !empty($pelanggan->no_hp)) {
            $detail_flags = $this->get_detail_benefit_flags($cart);
            $wa_link = $this->build_confirmation_wa_link(
                $pelanggan->no_hp,
                $invoice,
                $pelanggan->nama,
                date('Y-m-d H:i:s'),
                $tgl_selesai,
                $cart,
                $total_tagihan,
                [
                    'mode' => 'awal',
                    'status' => $data_transaksi['status'],
                    'dibayar' => $data_transaksi['dibayar'],
                    'poin_member_pelanggan' => $pelanggan->poin_member ?? 0,
                    'poin_diberikan_pada' => null,
                    'punya_cuci_komplit' => $detail_flags['punya_cuci_komplit'],
                    'punya_layanan_reward' => $detail_flags['punya_layanan_reward'],
                    'show_potential_poin' => true,
                ]
            );
        }

        $this->clear_cart_session();
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

        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.no_hp, m_pelanggan.poin_member as poin_member_pelanggan, m_metode_bayar.nama as nama_metode_bayar');
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
        $detail_flags = $this->get_detail_benefit_flags($data['active_detail']);
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

        $data['reward_preview'] = $this->hitung_reward_member_pembayaran($data['transaksi']->id);
        $data['promo_gratis_preview'] = $this->hitung_promo_gratis_pembayaran($data['transaksi']->id);
        $data['ringkasan_pembayaran'] = $this->siapkan_ringkasan_pembayaran(
            $data['transaksi'],
            $total_tagihan,
            $data['reward_preview'],
            true,
            $data['promo_gratis_preview']
        );
        $data['benefit_badge'] = $this->siapkan_badge_benefit_transaksi(
            $data['transaksi'],
            $data['active_detail'],
            $data['reward_preview'],
            $data['promo_gratis_preview']
        );
        $data['grand_total'] = $total_tagihan;

        $data['wa_confirmation_link'] = $this->build_confirmation_wa_link(
            $data['transaksi']->no_hp ?? '',
            $data['transaksi']->kode_invoice,
            $data['transaksi']->nama_pelanggan,
            $data['transaksi']->tgl_masuk,
            $data['transaksi']->batas_waktu,
            $data['active_detail'],
            $total_tagihan,
            [
                'dibayar' => $data['transaksi']->dibayar ?? '',
                'nama_metode_bayar' => $data['transaksi']->nama_metode_bayar ?? '',
                'tgl_bayar' => $data['transaksi']->tgl_bayar ?? '',
                'reward_member_dipakai' => $data['transaksi']->reward_member_dipakai ?? 0,
                'reward_gratis_qty' => $data['transaksi']->reward_gratis_qty ?? 0,
                'reward_potongan' => $data['transaksi']->reward_potongan ?? 0,
                'promo_gratis_dipakai' => $data['transaksi']->promo_gratis_dipakai ?? 0,
                'promo_gratis_qty' => $data['transaksi']->promo_gratis_qty ?? 0,
                'promo_gratis_potongan' => $data['transaksi']->promo_gratis_potongan ?? 0,
                'promo_gratis_keterangan' => $data['transaksi']->promo_gratis_keterangan ?? '',
                'mode' => 'detail',
                'status' => $data['transaksi']->status ?? '',
                'poin_member_pelanggan' => $data['transaksi']->poin_member_pelanggan ?? 0,
                'poin_diberikan_pada' => $data['transaksi']->poin_diberikan_pada ?? null,
                'punya_cuci_komplit' => $detail_flags['punya_cuci_komplit'],
                'punya_layanan_reward' => $detail_flags['punya_layanan_reward'],
                'show_potential_poin' => true,
            ]
        );

        $package_data = $this->load_package_form_data();
        $data['promo_settings'] = $package_data['promo_settings'];
        $data['member_settings'] = $this->get_member_settings();
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
        $this->sinkron_poin_member($trx->id);

        $this->session->set_flashdata('success', 'Status Laundry berhasil diupdate menjadi: ' . strtoupper($status_baru));
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function bayar_tagihan($kode_invoice)
    {
        $id_metode_bayar = (int) $this->input->post('id_metode_bayar');
        $tgl_bayar = date('Y-m-d H:i:s');

        $this->db->select('id, kode_invoice, status, dibayar, tgl_bayar, id_pelanggan, reward_member_dipakai, promo_gratis_dipakai');
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

        $reward_preview = $this->hitung_reward_member_pembayaran($trx->id);
        $reward_dikunci = false;
        $reward_gratis_qty = 0;
        $reward_potongan = 0;
        $promo_preview = null;
        $promo_dikunci = false;
        $promo_gratis_qty = 0;
        $promo_gratis_potongan = 0;
        $promo_gratis_keterangan = null;

        $this->db->trans_begin();

        if (!empty($reward_preview['reward_tersedia']) && (int) ($reward_preview['id_pelanggan'] ?? 0) > 0) {
            $this->db->set('poin_member', 0);
            $this->db->where('id', (int) $reward_preview['id_pelanggan']);
            $this->db->where('COALESCE(poin_member, 0) >= ' . (int) $this->batas_poin_member, null, false);
            $this->db->update('m_pelanggan');

            if ($this->db->affected_rows() > 0) {
                $reward_dikunci = true;
                $reward_gratis_qty = (float) ($reward_preview['reward_gratis_qty'] ?? 0);
                $reward_potongan = (int) ($reward_preview['reward_potongan'] ?? 0);
            }
        }

        if (!$reward_dikunci) {
            $promo_preview = $this->hitung_promo_gratis_pembayaran($trx->id);

            if (!empty($promo_preview['promo_tersedia'])) {
                $promo_dikunci = true;
                $promo_gratis_qty = (float) ($promo_preview['promo_gratis_qty'] ?? 0);
                $promo_gratis_potongan = (int) ($promo_preview['promo_gratis_potongan'] ?? 0);
                $promo_gratis_keterangan = trim((string) ($promo_preview['promo_gratis_keterangan'] ?? ''));
            }
        }

        $data_update = [
            'dibayar' => $this->allowed_payment_statuses[1],
            'tgl_bayar' => $tgl_bayar,
            'id_metode_bayar' => $id_metode_bayar
        ];

        if ($reward_dikunci) {
            $data_update['reward_member_dipakai'] = 1;
            $data_update['reward_gratis_qty'] = $reward_gratis_qty;
            $data_update['reward_potongan'] = $reward_potongan;
        }

        if ($promo_dikunci) {
            $data_update['promo_gratis_dipakai'] = 1;
            $data_update['promo_gratis_qty'] = $promo_gratis_qty;
            $data_update['promo_gratis_potongan'] = $promo_gratis_potongan;
            $data_update['promo_gratis_keterangan'] = $promo_gratis_keterangan;
        }

        $this->db->where('id', (int) $trx->id);
        $this->db->where('dibayar', 'Belum Dibayar');
        $this->db->update('transaksi', $data_update);

        if ($this->db->affected_rows() <= 0) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Pembayaran gagal dicatat karena status transaksi sudah berubah. Silakan muat ulang halaman ini.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Pembayaran gagal dicatat. Silakan coba lagi.');
            redirect('transaksi/detail/' . $kode_invoice);
        }

        $this->db->trans_commit();
        $this->sinkron_poin_member($trx->id);

        $this->session->set_flashdata('success', 'Pembayaran berhasil dicatat. Status pengambilan tetap terpisah.');
        redirect('transaksi/detail/' . $kode_invoice);
    }

    public function tandai_diambil($kode_invoice)
    {
        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.no_hp, m_pelanggan.poin_member as poin_member_pelanggan, m_metode_bayar.nama as nama_metode_bayar');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('m_metode_bayar', 'm_metode_bayar.id = transaksi.id_metode_bayar', 'left');
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

        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.no_hp, m_pelanggan.poin_member as poin_member_pelanggan, m_metode_bayar.nama as nama_metode_bayar');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('m_metode_bayar', 'm_metode_bayar.id = transaksi.id_metode_bayar', 'left');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        $trx = $this->db->get()->row();

        $details = $this->get_active_transaction_details($trx->id);
        $detail_flags = $this->get_detail_benefit_flags($details);

        $wa_link = "";

        if ($trx && !empty($trx->no_hp)) {
            $nama_kasir = $this->session->userdata('username');
            if (empty($nama_kasir)) {
                $nama_kasir = 'Admin';
            }

            $total_bayar = 0;
            foreach ($details as $d) {
                $total_bayar += (float) ($d->subtotal ?? 0);
            }

            $wa_link = $this->build_confirmation_wa_link(
                $trx->no_hp,
                $trx->kode_invoice,
                $trx->nama_pelanggan,
                $trx->tgl_masuk,
                $trx->batas_waktu,
                $details,
                $total_bayar,
                [
                    'mode' => 'pengambilan',
                    'status' => $trx->status ?? '',
                    'dibayar' => $trx->dibayar ?? '',
                    'tgl_bayar' => $trx->tgl_bayar ?? '',
                    'tgl_diambil' => $trx->tgl_diambil ?? '',
                    'nama_metode_bayar' => $trx->nama_metode_bayar ?? '',
                    'nama_kasir' => $nama_kasir,
                    'reward_member_dipakai' => $trx->reward_member_dipakai ?? 0,
                    'reward_gratis_qty' => $trx->reward_gratis_qty ?? 0,
                    'reward_potongan' => $trx->reward_potongan ?? 0,
                    'promo_gratis_dipakai' => $trx->promo_gratis_dipakai ?? 0,
                    'promo_gratis_qty' => $trx->promo_gratis_qty ?? 0,
                    'promo_gratis_potongan' => $trx->promo_gratis_potongan ?? 0,
                    'promo_gratis_keterangan' => $trx->promo_gratis_keterangan ?? '',
                    'poin_member_pelanggan' => $trx->poin_member_pelanggan ?? 0,
                    'poin_diberikan_pada' => $trx->poin_diberikan_pada ?? null,
                    'punya_cuci_komplit' => $detail_flags['punya_cuci_komplit'],
                    'punya_layanan_reward' => $detail_flags['punya_layanan_reward'],
                    'show_potential_poin' => false,
                ]
            );
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
        $total_tagihan = 0;
        foreach ($data['detail'] as $detail_row) {
            $total_tagihan += (float) ($detail_row->subtotal ?? 0);
        }
        $data['ringkasan_pembayaran'] = $this->siapkan_ringkasan_pembayaran($data['transaksi'], $total_tagihan, null, false);

        $data['company'] = $this->company;
        $this->load->view('transaksi/cetak', $data);
    }
}
