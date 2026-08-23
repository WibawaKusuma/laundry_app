<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi_model extends CI_Model
{
    private $cancelled_status = 'Dibatalkan';

    private function get_subquery_total_detail_aktif($alias = 'detail_aktif')
    {
        return "(SELECT dt.id_transaksi, SUM(dt.qty * dt.harga) AS subtotal_normal
            FROM transaksi_detail dt
            WHERE COALESCE(dt.batal, 0) = 0
            GROUP BY dt.id_transaksi) {$alias}";
    }

    private function get_total_akhir_expr($subtotal_field, $reward_field, $promo_field = '0')
    {
        return "GREATEST(COALESCE({$subtotal_field}, 0) - COALESCE({$reward_field}, 0) - COALESCE({$promo_field}, 0), 0)";
    }

    private function get_snapshot_subtotal_expr($transaksi_alias, $fallback_subtotal_field)
    {
        return "(CASE
            WHEN {$transaksi_alias}.snapshot_tagihan_at IS NOT NULL THEN COALESCE({$transaksi_alias}.subtotal_normal, 0)
            ELSE COALESCE({$fallback_subtotal_field}, 0)
        END)";
    }

    private function get_snapshot_diskon_expr($transaksi_alias, $fallback_reward_field, $fallback_promo_field = '0')
    {
        return "(CASE
            WHEN {$transaksi_alias}.snapshot_tagihan_at IS NOT NULL THEN COALESCE({$transaksi_alias}.diskon_total, 0)
            ELSE COALESCE({$fallback_reward_field}, 0) + COALESCE({$fallback_promo_field}, 0)
        END)";
    }

    private function get_snapshot_total_expr($transaksi_alias, $fallback_subtotal_field, $fallback_reward_field, $fallback_promo_field = '0')
    {
        $fallback_total_expr = $this->get_total_akhir_expr($fallback_subtotal_field, $fallback_reward_field, $fallback_promo_field);

        return "(CASE
            WHEN {$transaksi_alias}.snapshot_tagihan_at IS NOT NULL THEN COALESCE({$transaksi_alias}.total_akhir, 0)
            ELSE {$fallback_total_expr}
        END)";
    }

    private function get_report_reward_expr($transaksi_alias)
    {
        return "(CASE
            WHEN {$transaksi_alias}.snapshot_tagihan_at IS NOT NULL AND {$transaksi_alias}.benefit_tipe = 'reward_member' THEN COALESCE({$transaksi_alias}.diskon_total, 0)
            ELSE COALESCE({$transaksi_alias}.reward_potongan, 0)
        END)";
    }

    private function get_report_promo_expr($transaksi_alias)
    {
        return "(CASE
            WHEN {$transaksi_alias}.snapshot_tagihan_at IS NOT NULL AND {$transaksi_alias}.benefit_tipe IN ('promo_daily', 'promo_sepatu') THEN COALESCE({$transaksi_alias}.diskon_total, 0)
            ELSE COALESCE({$transaksi_alias}.promo_gratis_potongan, 0)
        END)";
    }

    private function apply_not_cancelled($alias = 'transaksi')
    {
        $field = $alias ? $alias . '.status' : 'status';
        $this->db->where("(COALESCE({$field}, '') <> " . $this->db->escape($this->cancelled_status) . ")", null, false);
    }

    public function get_pelanggan_member_data($id_pelanggan)
    {
        return $this->db
            ->select('id, nama, aktif, poin_member')
            ->where('id', (int) $id_pelanggan)
            ->get('m_pelanggan')
            ->row();
    }

    public function get_pelanggan_aktif()
    {
        $this->db->where('aktif', 1);
        $this->db->order_by('nama', 'ASC');
        return $this->db->get('m_pelanggan')->result();
    }

    public function get_pelanggan_by_id($id_pelanggan)
    {
        return $this->db->get_where('m_pelanggan', ['id' => (int) $id_pelanggan])->row();
    }

    public function get_pelanggan_aktif_by_id($id_pelanggan)
    {
        return $this->db
            ->where('id', (int) $id_pelanggan)
            ->where('aktif', 1)
            ->get('m_pelanggan')
            ->row();
    }

    public function get_kategori()
    {
        return $this->db->get('m_kategori')->result();
    }

    public function get_tipe()
    {
        return $this->db->get('m_tipe')->result();
    }

    public function get_paket_form_options()
    {
        $this->db->select('m_paket_laundry.*, m_satuan.nama_satuan, m_kategori.nama_kategori, m_kategori.id_kategori as id_kat, m_tipe.nama_tipe, m_tipe.id_tipe as id_tp');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        return $this->db->get()->result();
    }

    public function get_package_with_meta($id_paket)
    {
        $this->db->select('m_paket_laundry.*, m_satuan.nama_satuan, m_kategori.nama_kategori, m_tipe.nama_tipe');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        $this->db->where('id_paket_laundry', (int) $id_paket);
        return $this->db->get()->row();
    }

    public function get_paket_by_id($id_paket)
    {
        return $this->db->get_where('m_paket_laundry', ['id_paket_laundry' => (int) $id_paket])->row();
    }

    public function get_detail_transaction_by_invoice($kode_invoice)
    {
        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.no_hp, m_pelanggan.poin_member as poin_member_pelanggan, m_metode_bayar.nama as nama_metode_bayar');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('m_metode_bayar', 'm_metode_bayar.id = transaksi.id_metode_bayar', 'left');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        return $this->db->get()->row();
    }

    public function get_print_transaction_by_invoice($kode_invoice)
    {
        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_pelanggan.alamat, m_metode_bayar.nama as nama_metode_bayar');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('m_metode_bayar', 'm_metode_bayar.id = transaksi.id_metode_bayar', 'left');
        $this->db->where('transaksi.kode_invoice', $kode_invoice);
        return $this->db->get()->row();
    }

    public function get_basic_transaction_by_invoice($kode_invoice)
    {
        $this->db->select('id, kode_invoice, status, dibayar');
        $this->db->from('transaksi');
        $this->db->where('kode_invoice', $kode_invoice);
        return $this->db->get()->row();
    }

    public function get_status_transaction_by_invoice($kode_invoice)
    {
        $this->db->select('id, status, dibayar, tgl_selesai, tgl_diambil');
        $this->db->from('transaksi');
        $this->db->where('kode_invoice', $kode_invoice);
        return $this->db->get()->row();
    }

    public function get_payment_transaction_by_invoice($kode_invoice)
    {
        $this->db->select('id, kode_invoice, status, dibayar, tgl_bayar, id_pelanggan, reward_member_dipakai, promo_gratis_dipakai, aturan_promo_tipe, aturan_promo_min_qty, aturan_promo_gratis_qty, aturan_promo_unit, aturan_promo_label');
        $this->db->from('transaksi');
        $this->db->where('kode_invoice', $kode_invoice);
        return $this->db->get()->row();
    }

    public function get_point_sync_transaction($id_transaksi)
    {
        $this->db->select('id, id_pelanggan, status, dibayar, poin_diberikan_pada, reward_member_dipakai');
        $this->db->from('transaksi');
        $this->db->where('id', (int) $id_transaksi);
        return $this->db->get()->row();
    }

    public function sync_member_point($id_transaksi, $id_pelanggan, $batas_poin_member)
    {
        $this->db->trans_start();

        $this->db->set('poin_diberikan_pada', date('Y-m-d H:i:s'));
        $this->db->where('id', (int) $id_transaksi);
        $this->db->where('poin_diberikan_pada IS NULL', null, false);
        $this->db->where_in('status', ['Selesai', 'Diambil']);
        $this->db->where('dibayar', 'Sudah Dibayar');
        $this->db->where('COALESCE(reward_member_dipakai, 0) = 0', null, false);
        $this->db->update('transaksi');

        $poin_berhasil_dikunci = $this->db->affected_rows() > 0;

        if ($poin_berhasil_dikunci) {
            $this->db->set('poin_member', 'LEAST(COALESCE(poin_member, 0) + 1, ' . (int) $batas_poin_member . ')', false);
            $this->db->where('id', (int) $id_pelanggan);
            $this->db->update('m_pelanggan');
        }

        $this->db->trans_complete();

        return $poin_berhasil_dikunci && $this->db->trans_status();
    }

    public function get_reward_payment_transaction($id_transaksi)
    {
        $this->db->select('id, id_pelanggan, status, dibayar, reward_member_dipakai');
        $this->db->from('transaksi');
        $this->db->where('id', (int) $id_transaksi);
        return $this->db->get()->row();
    }

    public function get_promo_payment_transaction($id_transaksi)
    {
        $this->db->select('id, status, dibayar, reward_member_dipakai, promo_gratis_dipakai, aturan_promo_tipe, aturan_promo_min_qty, aturan_promo_gratis_qty, aturan_promo_unit, aturan_promo_label');
        $this->db->from('transaksi');
        $this->db->where('id', (int) $id_transaksi);
        return $this->db->get()->row();
    }

    public function get_detail_item($detail_id, $transaksi_id)
    {
        return $this->db->get_where('transaksi_detail', [
            'id' => (int) $detail_id,
            'id_transaksi' => (int) $transaksi_id,
        ])->row();
    }

    public function update_detail_note($detail_id, $keterangan)
    {
        $this->db->where('id', (int) $detail_id);
        return $this->db->update('transaksi_detail', [
            'keterangan' => $keterangan,
        ]);
    }

    public function update_detail_item($detail_id, array $item)
    {
        $this->db->where('id', (int) $detail_id);
        return $this->db->update('transaksi_detail', [
            'qty' => $item['charged_qty'],
            'harga' => $item['harga'],
            'keterangan' => $item['keterangan'],
        ]);
    }

    public function update_billing_snapshot($transaksi_id, array $snapshot)
    {
        $allowed_fields = [
            'subtotal_normal',
            'diskon_total',
            'total_akhir',
            'benefit_tipe',
            'benefit_keterangan',
            'snapshot_tagihan_at',
            'aturan_promo_tipe',
            'aturan_promo_min_qty',
            'aturan_promo_gratis_qty',
            'aturan_promo_unit',
            'aturan_promo_label',
        ];
        $data_update = [];

        foreach ($allowed_fields as $field) {
            if (array_key_exists($field, $snapshot)) {
                $data_update[$field] = $snapshot[$field];
            }
        }

        if (empty($data_update)) {
            return false;
        }

        $this->db->where('id', (int) $transaksi_id);
        return $this->db->update('transaksi', $data_update);
    }

    public function cancel_detail_item($detail_id, $user_id)
    {
        $this->db->where('id', (int) $detail_id);
        return $this->db->update('transaksi_detail', [
            'batal' => 1,
            'batal_at' => date('Y-m-d H:i:s'),
            'batal_by' => (int) $user_id,
        ]);
    }

    public function cancel_transaction($transaksi_id, $user_id, $alasan_batal)
    {
        $this->db->trans_begin();

        $now = date('Y-m-d H:i:s');
        $alasan_batal = trim((string) $alasan_batal);

        $this->db->where('id', (int) $transaksi_id);
        $this->db->where('status', 'Baru');
        $this->db->where('dibayar', 'Belum Dibayar');
        $this->db->update('transaksi', [
            'status' => $this->cancelled_status,
            'batal_at' => $now,
            'batal_by' => (int) $user_id,
            'alasan_batal' => $alasan_batal,
            'batas_waktu' => null,
            'tgl_selesai' => null,
            'tgl_bayar' => null,
            'tgl_diambil' => null,
            'id_metode_bayar' => null,
            'reward_member_dipakai' => 0,
            'reward_gratis_qty' => 0,
            'reward_potongan' => 0,
            'promo_gratis_dipakai' => 0,
            'promo_gratis_qty' => 0,
            'promo_gratis_potongan' => 0,
            'promo_gratis_keterangan' => null,
            'subtotal_normal' => 0,
            'diskon_total' => 0,
            'total_akhir' => 0,
            'benefit_tipe' => null,
            'benefit_keterangan' => null,
            'snapshot_tagihan_at' => $now,
        ]);

        if ($this->db->affected_rows() <= 0) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->where('id_transaksi', (int) $transaksi_id);
        $this->db->where('COALESCE(batal, 0) = 0', null, false);
        $this->db->update('transaksi_detail', [
            'batal' => 1,
            'batal_at' => $now,
            'batal_by' => (int) $user_id,
            'alasan_batal' => $alasan_batal,
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();

        return true;
    }

    public function insert_detail_item(array $item)
    {
        return $this->db->insert('transaksi_detail', $item);
    }

    public function create_transaction_with_details(array $data_transaksi, array $data_detail)
    {
        $this->db->trans_start();

        $this->db->insert('transaksi', $data_transaksi);
        $id_transaksi = $this->db->insert_id();

        foreach ($data_detail as &$detail) {
            $detail['id_transaksi'] = $id_transaksi;
        }
        unset($detail);

        if (!empty($data_detail)) {
            $this->db->insert_batch('transaksi_detail', $data_detail);
        }

        $this->db->trans_complete();

        return [
            'success' => $this->db->trans_status(),
            'id_transaksi' => $id_transaksi,
        ];
    }

    public function update_status_by_invoice($kode_invoice, array $data_update)
    {
        $this->db->where('kode_invoice', $kode_invoice);
        return $this->db->update('transaksi', $data_update);
    }

    public function mark_taken_by_invoice($kode_invoice, array $data_update)
    {
        $this->db->where('kode_invoice', $kode_invoice);
        return $this->db->update('transaksi', $data_update);
    }

    public function record_payment($transaksi_id, array $base_update, array $reward_preview, array $promo_preview, $batas_poin_member)
    {
        $this->db->trans_begin();

        $reward_dikunci = false;
        $promo_dikunci = false;
        if (!empty($reward_preview['reward_tersedia']) && (int) ($reward_preview['id_pelanggan'] ?? 0) > 0) {
            $this->db->set('poin_member', 0);
            $this->db->where('id', (int) $reward_preview['id_pelanggan']);
            $this->db->where('COALESCE(poin_member, 0) >= ' . (int) $batas_poin_member, null, false);
            $this->db->update('m_pelanggan');

            $reward_dikunci = $this->db->affected_rows() > 0;
        }

        $data_update = $base_update;

        if ($reward_dikunci) {
            $data_update['reward_member_dipakai'] = 1;
            $data_update['reward_gratis_qty'] = (float) ($reward_preview['reward_gratis_qty'] ?? 0);
            $data_update['reward_potongan'] = (int) ($reward_preview['reward_potongan'] ?? 0);
        } elseif (!empty($promo_preview['promo_tersedia'])) {
            $promo_dikunci = true;
            $data_update['promo_gratis_dipakai'] = 1;
            $data_update['promo_gratis_qty'] = (float) ($promo_preview['promo_gratis_qty'] ?? 0);
            $data_update['promo_gratis_potongan'] = (int) ($promo_preview['promo_gratis_potongan'] ?? 0);
            $data_update['promo_gratis_keterangan'] = trim((string) ($promo_preview['promo_gratis_keterangan'] ?? ''));
        }

        if (array_key_exists('subtotal_normal', $data_update)) {
            $subtotal_normal = (int) ($data_update['subtotal_normal'] ?? 0);

            if ($reward_dikunci) {
                $data_update['diskon_total'] = (int) ($data_update['reward_potongan'] ?? 0);
                $data_update['total_akhir'] = max(0, $subtotal_normal - (int) $data_update['diskon_total']);
                $data_update['benefit_tipe'] = 'reward_member';
                $data_update['benefit_keterangan'] = 'Reward Member gratis ' . (float) ($data_update['reward_gratis_qty'] ?? 0) . ' kg';
            } elseif ($promo_dikunci) {
                $data_update['diskon_total'] = (int) ($data_update['promo_gratis_potongan'] ?? 0);
                $data_update['total_akhir'] = max(0, $subtotal_normal - (int) $data_update['diskon_total']);
                $data_update['benefit_tipe'] = (string) ($promo_preview['benefit_tipe'] ?? 'promo_daily');
                $data_update['benefit_keterangan'] = trim((string) ($data_update['promo_gratis_keterangan'] ?? ''));
            } else {
                $data_update['diskon_total'] = 0;
                $data_update['total_akhir'] = $subtotal_normal;
                $data_update['benefit_tipe'] = null;
                $data_update['benefit_keterangan'] = null;
            }
        }

        $this->db->where('id', (int) $transaksi_id);
        $this->db->where('dibayar', 'Belum Dibayar');
        $this->db->update('transaksi', $data_update);

        if ($this->db->affected_rows() <= 0) {
            $this->db->trans_rollback();
            return [
                'success' => false,
                'reason' => 'status_changed',
                'reward_dikunci' => $reward_dikunci,
                'promo_dikunci' => $promo_dikunci,
            ];
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return [
                'success' => false,
                'reason' => 'transaction_failed',
                'reward_dikunci' => $reward_dikunci,
                'promo_dikunci' => $promo_dikunci,
            ];
        }

        $this->db->trans_commit();

        return [
            'success' => true,
            'reason' => null,
            'reward_dikunci' => $reward_dikunci,
            'promo_dikunci' => $promo_dikunci,
        ];
    }

    public function get_active_payment_methods()
    {
        $this->db->where('is_active', 1);
        return $this->db->get('m_metode_bayar')->result();
    }

    public function get_transaction_details_raw($transaksi_id, $include_cancelled = false)
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
            m_kategori.nama_kategori,
            m_satuan.nama_satuan
        ');
        $this->db->from('transaksi_detail');
        $this->db->join('m_paket_laundry', 'm_paket_laundry.id_paket_laundry = transaksi_detail.id_paket');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $this->db->where('transaksi_detail.id_transaksi', $transaksi_id);

        if (!$include_cancelled) {
            $this->db->where('COALESCE(transaksi_detail.batal, 0) = 0', null, false);
        }

        return $this->db->get()->result();
    }

    public function count_active_transaction_details($transaksi_id)
    {
        $this->db->from('transaksi_detail');
        $this->db->where('id_transaksi', $transaksi_id);
        $this->db->where('COALESCE(batal, 0) = 0', null, false);

        return (int) $this->db->count_all_results();
    }

    public function invoice_exists($invoice)
    {
        return $this->db
            ->where('kode_invoice', $invoice)
            ->count_all_results('transaksi') > 0;
    }

    public function get_deadline_source($transaksi_id)
    {
        $this->db->select('transaksi.id, transaksi.tgl_masuk');
        $this->db->from('transaksi');
        $this->db->where('transaksi.id', $transaksi_id);
        return $this->db->get()->row();
    }

    public function get_max_active_detail_duration($transaksi_id)
    {
        $this->db->select_max('m_paket_laundry.durasi_jam', 'max_jam');
        $this->db->from('transaksi_detail');
        $this->db->join('m_paket_laundry', 'm_paket_laundry.id_paket_laundry = transaksi_detail.id_paket');
        $this->db->where('transaksi_detail.id_transaksi', $transaksi_id);
        $this->db->where('COALESCE(transaksi_detail.batal, 0) = 0', null, false);
        $result = $this->db->get()->row();

        return (int) ($result->max_jam ?? 0);
    }

    public function update_deadline($transaksi_id, $batas_waktu)
    {
        $this->db->where('id', $transaksi_id);
        return $this->db->update('transaksi', ['batas_waktu' => $batas_waktu]);
    }

    public function count_by_payment_status($payment_status, $tgl_awal, $tgl_akhir)
    {
        $this->apply_not_cancelled(null);
        $this->db->where('dibayar', $payment_status);
        $this->db->where('DATE(tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(tgl_masuk) <=', $tgl_akhir);
        return $this->db->count_all_results('transaksi');
    }

    public function count_paid_ready_pickup($tgl_awal, $tgl_akhir)
    {
        $this->apply_not_cancelled(null);
        $this->db->where('status', 'Selesai');
        $this->db->where('dibayar', 'Sudah Dibayar');
        $this->db->where('DATE(tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(tgl_masuk) <=', $tgl_akhir);
        return $this->db->count_all_results('transaksi');
    }

    public function sum_omset($tgl_awal, $tgl_akhir)
    {
        $total_akhir_expr = $this->get_snapshot_total_expr('transaksi', 'detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select("SUM({$total_akhir_expr}) as total_nilai", false);
        $this->db->from('transaksi');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);
        $this->apply_not_cancelled('transaksi');
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
    }

    public function sum_omset_bulanan($tahun, $bulan)
    {
        $total_akhir_expr = $this->get_snapshot_total_expr('transaksi', 'detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select("SUM({$total_akhir_expr}) as total_nilai", false);
        $this->db->from('transaksi');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);
        $this->apply_not_cancelled('transaksi');
        $this->db->where('YEAR(transaksi.tgl_masuk)', $tahun);
        $this->db->where('MONTH(transaksi.tgl_masuk)', $bulan);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
    }

    public function count_order_bulanan($tahun, $bulan)
    {
        $this->apply_not_cancelled(null);
        $this->db->where('YEAR(tgl_masuk)', $tahun);
        $this->db->where('MONTH(tgl_masuk)', $bulan);
        return $this->db->count_all_results('transaksi');
    }

    public function sum_kas_masuk($tgl_awal, $tgl_akhir)
    {
        $total_akhir_expr = $this->get_snapshot_total_expr('transaksi', 'detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select("SUM({$total_akhir_expr}) as total_nilai", false);
        $this->db->from('transaksi');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);
        $this->apply_not_cancelled('transaksi');
        $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
        $this->db->where('transaksi.tgl_bayar IS NOT NULL', null, false);
        $this->db->where('DATE(transaksi.tgl_bayar) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_bayar) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
    }

    public function sum_piutang($tgl_awal, $tgl_akhir)
    {
        $total_akhir_expr = $this->get_snapshot_total_expr('transaksi', 'detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select("SUM({$total_akhir_expr}) as total_nilai", false);
        $this->db->from('transaksi');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);
        $this->apply_not_cancelled('transaksi');
        $this->db->where('transaksi.dibayar', 'Belum Dibayar');
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
    }

    public function sum_reward_member($tgl_awal, $tgl_akhir)
    {
        $this->db->select('
            COALESCE(SUM(transaksi.reward_potongan), 0) as total_reward_potongan,
            COALESCE(SUM(transaksi.reward_gratis_qty), 0) as total_reward_gratis_qty
        ', false);
        $this->db->from('transaksi');
        $this->apply_not_cancelled('transaksi');
        $this->db->where('transaksi.reward_member_dipakai', 1);
        $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
        $this->db->where('transaksi.tgl_bayar IS NOT NULL', null, false);
        $this->db->where('DATE(transaksi.tgl_bayar) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_bayar) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return [
            'total_reward_potongan' => (float) ($result->total_reward_potongan ?? 0),
            'total_reward_gratis_qty' => (float) ($result->total_reward_gratis_qty ?? 0),
        ];
    }

    public function sum_promo_gratis($tgl_awal, $tgl_akhir)
    {
        $this->db->select('
            COALESCE(SUM(transaksi.promo_gratis_potongan), 0) as total_promo_gratis_potongan,
            COALESCE(SUM(transaksi.promo_gratis_qty), 0) as total_promo_gratis_qty
        ', false);
        $this->db->from('transaksi');
        $this->apply_not_cancelled('transaksi');
        $this->db->where('transaksi.promo_gratis_dipakai', 1);
        $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
        $this->db->where('transaksi.tgl_bayar IS NOT NULL', null, false);
        $this->db->where('DATE(transaksi.tgl_bayar) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_bayar) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return [
            'total_promo_gratis_potongan' => (float) ($result->total_promo_gratis_potongan ?? 0),
            'total_promo_gratis_qty' => (float) ($result->total_promo_gratis_qty ?? 0),
        ];
    }

    // Hitung jumlah berdasarkan status DAN rentang tanggal
    public function count_by_status($status, $tgl_awal, $tgl_akhir)
    {
        $this->apply_not_cancelled(null);
        $this->db->where('status', $status);
        $this->db->where('DATE(tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(tgl_masuk) <=', $tgl_akhir);
        return $this->db->count_all_results('transaksi');
    }

    // Ambil daftar transaksi berdasarkan rentang tanggal
    public function get_terbaru($tgl_awal = null, $tgl_akhir = null)
    {
        $this->db->select('t.*, p.nama');
        $this->db->from('transaksi t');
        $this->db->join('m_pelanggan p', 't.id_pelanggan = p.id');
        $this->apply_not_cancelled('t');

        // Filter Tanggal
        if ($tgl_awal && $tgl_akhir) {
            $this->db->where('DATE(t.tgl_masuk) >=', $tgl_awal);
            $this->db->where('DATE(t.tgl_masuk) <=', $tgl_akhir);
        }

        $this->db->order_by('t.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_tanggal($tgl_awal, $tgl_akhir)
    {
        $subtotal_expr = $this->get_snapshot_subtotal_expr('t', 'detail_aktif.subtotal_normal');
        $reward_expr = $this->get_report_reward_expr('t');
        $promo_expr = $this->get_report_promo_expr('t');
        $total_akhir_expr = $this->get_snapshot_total_expr('t', 'detail_aktif.subtotal_normal', 't.reward_potongan', 't.promo_gratis_potongan');

        $this->db->select('
            t.*,
            p.nama as nama_pelanggan,
            ' . $subtotal_expr . ' as subtotal_normal,
            ' . $reward_expr . ' as reward_potongan,
            COALESCE(t.reward_gratis_qty, 0) as reward_gratis_qty,
            ' . $promo_expr . ' as promo_gratis_potongan,
            COALESCE(t.promo_gratis_qty, 0) as promo_gratis_qty,
            COALESCE(t.promo_gratis_keterangan, "") as promo_gratis_keterangan,
            ' . $total_akhir_expr . ' as total_akhir,
            ' . $total_akhir_expr . ' as total_harga
        ', false);
        $this->db->from('transaksi t');
        $this->db->join('m_pelanggan p', 't.id_pelanggan = p.id');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = t.id', 'left', false);
        $this->apply_not_cancelled('t');
        $this->db->where('DATE(t.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(t.tgl_masuk) <=', $tgl_akhir);
        $this->db->order_by('t.id', 'DESC');

        return $this->db->get()->result();
    }

    public function get_index_by_periode($tgl_awal, $tgl_akhir, $status_bayar = 'belum')
    {
        $jenis_cucian_subquery = '(
            SELECT
                td.id_transaksi,
                GROUP_CONCAT(
                    DISTINCT CASE
                        WHEN LOWER(TRIM(p.nama_paket)) LIKE "%express%" THEN "Express"
                        WHEN LOWER(TRIM(p.nama_paket)) LIKE "%satu hari%" THEN "Satu hari"
                        WHEN LOWER(TRIM(p.nama_paket)) LIKE "%reguler%" THEN "Reguler"
                        ELSE NULL
                    END
                    ORDER BY p.nama_paket
                    SEPARATOR ", "
                ) AS jenis_cucian
            FROM transaksi_detail td
            INNER JOIN m_paket_laundry p ON p.id_paket_laundry = td.id_paket
            WHERE COALESCE(td.batal, 0) = 0
            GROUP BY td.id_transaksi
        ) jenis_cucian';

        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, jenis_cucian.jenis_cucian');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join($jenis_cucian_subquery, 'jenis_cucian.id_transaksi = transaksi.id', 'left', false);
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);

        if ($status_bayar === 'sudah') {
            $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
        } elseif ($status_bayar === 'belum') {
            $this->db->where('transaksi.dibayar !=', 'Sudah Dibayar');
        }

        $this->db->order_by('transaksi.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_laporan($tgl_awal, $tgl_akhir, $jenis_laporan = 'omset', $status_bayar = 'semua')
    {
        $subtotal_expr = $this->get_snapshot_subtotal_expr('transaksi', 'detail_aktif.subtotal_normal');
        $reward_expr = $this->get_report_reward_expr('transaksi');
        $promo_expr = $this->get_report_promo_expr('transaksi');
        $total_akhir_expr = $this->get_snapshot_total_expr('transaksi', 'detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select('
            transaksi.*,
            m_pelanggan.nama as nama_pelanggan,
            m_metode_bayar.nama as nama_metode_bayar,
            ' . $subtotal_expr . ' as subtotal_normal,
            ' . $reward_expr . ' as reward_potongan,
            COALESCE(transaksi.reward_gratis_qty, 0) as reward_gratis_qty,
            ' . $promo_expr . ' as promo_gratis_potongan,
            COALESCE(transaksi.promo_gratis_qty, 0) as promo_gratis_qty,
            COALESCE(transaksi.promo_gratis_keterangan, "") as promo_gratis_keterangan,
            ' . $total_akhir_expr . ' as total_akhir,
            ' . $total_akhir_expr . ' as total_harga
        ', false);
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('m_metode_bayar', 'm_metode_bayar.id = transaksi.id_metode_bayar', 'left');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);
        $this->apply_not_cancelled('transaksi');

        switch ($jenis_laporan) {
            case 'kas_masuk':
                $this->db->where('DATE(transaksi.tgl_bayar) >=', $tgl_awal);
                $this->db->where('DATE(transaksi.tgl_bayar) <=', $tgl_akhir);
                $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
                $this->db->where('transaksi.tgl_bayar IS NOT NULL', null, false);
                $this->db->order_by('transaksi.tgl_bayar', 'ASC');
                break;

            case 'piutang':
                $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
                $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
                $this->db->where('transaksi.dibayar', 'Belum Dibayar');
                $this->db->order_by('transaksi.tgl_masuk', 'ASC');
                break;

            case 'pengambilan':
                $this->db->where('DATE(transaksi.tgl_diambil) >=', $tgl_awal);
                $this->db->where('DATE(transaksi.tgl_diambil) <=', $tgl_akhir);
                $this->db->where('transaksi.status', 'Diambil');
                $this->db->where('transaksi.tgl_diambil IS NOT NULL', null, false);
                $this->db->order_by('transaksi.tgl_diambil', 'ASC');
                break;

            case 'omset':
            default:
                $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
                $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);

                if ($status_bayar === 'lunas') {
                    $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
                } elseif ($status_bayar === 'belum') {
                    $this->db->where('transaksi.dibayar', 'Belum Dibayar');
                }

                $this->db->order_by('transaksi.tgl_masuk', 'ASC');
                break;
        }

        return $this->db->get()->result();
    }

    public function get_avg_kg_per_hari($tgl_awal, $tgl_akhir)
    {
        $this->db->select('SUM(td.qty) as total_kg');
        $this->db->from('transaksi t');
        $this->db->join('transaksi_detail td', 'td.id_transaksi = t.id');
        $this->db->join('m_paket_laundry p', 'p.id_paket_laundry = td.id_paket');
        $this->db->where('DATE(t.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(t.tgl_masuk) <=', $tgl_akhir);
        $this->db->where('p.id_satuan', 1); // KG
        $this->db->where('p.id_kategori !=', 3); // Bukan Satuan Khusus
        $this->db->where('COALESCE(td.batal, 0) =', 0);
        $result = $this->db->get()->row();

        $total_kg = (float) ($result->total_kg ?? 0);

        // Hitung selisih hari kalender
        $start = new DateTime($tgl_awal);
        $end = new DateTime($tgl_akhir);
        $diff = $start->diff($end);
        $days = $diff->days + 1;

        return $days > 0 ? ($total_kg / $days) : 0;
    }
}

