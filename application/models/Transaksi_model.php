<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi_model extends CI_Model
{
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

    public function count_by_payment_status($payment_status, $tgl_awal, $tgl_akhir)
    {
        $this->db->where('dibayar', $payment_status);
        $this->db->where('DATE(tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(tgl_masuk) <=', $tgl_akhir);
        return $this->db->count_all_results('transaksi');
    }

    public function count_paid_ready_pickup($tgl_awal, $tgl_akhir)
    {
        $this->db->where('status', 'Selesai');
        $this->db->where('dibayar', 'Sudah Dibayar');
        $this->db->where('DATE(tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(tgl_masuk) <=', $tgl_akhir);
        return $this->db->count_all_results('transaksi');
    }

    public function sum_omset($tgl_awal, $tgl_akhir)
    {
        $total_akhir_expr = $this->get_total_akhir_expr('detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select("SUM({$total_akhir_expr}) as total_nilai", false);
        $this->db->from('transaksi');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
    }

    public function sum_omset_bulanan($tahun, $bulan)
    {
        $total_akhir_expr = $this->get_total_akhir_expr('detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select("SUM({$total_akhir_expr}) as total_nilai", false);
        $this->db->from('transaksi');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);
        $this->db->where('YEAR(transaksi.tgl_masuk)', $tahun);
        $this->db->where('MONTH(transaksi.tgl_masuk)', $bulan);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
    }

    public function sum_kas_masuk($tgl_awal, $tgl_akhir)
    {
        $total_akhir_expr = $this->get_total_akhir_expr('detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select("SUM({$total_akhir_expr}) as total_nilai", false);
        $this->db->from('transaksi');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);
        $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
        $this->db->where('transaksi.tgl_bayar IS NOT NULL', null, false);
        $this->db->where('DATE(transaksi.tgl_bayar) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_bayar) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
    }

    public function sum_piutang($tgl_awal, $tgl_akhir)
    {
        $total_akhir_expr = $this->get_total_akhir_expr('detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select("SUM({$total_akhir_expr}) as total_nilai", false);
        $this->db->from('transaksi');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);
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
        $total_akhir_expr = $this->get_total_akhir_expr('detail_aktif.subtotal_normal', 't.reward_potongan', 't.promo_gratis_potongan');

        $this->db->select('
            t.*,
            p.nama as nama_pelanggan,
            COALESCE(detail_aktif.subtotal_normal, 0) as subtotal_normal,
            COALESCE(t.reward_potongan, 0) as reward_potongan,
            COALESCE(t.reward_gratis_qty, 0) as reward_gratis_qty,
            COALESCE(t.promo_gratis_potongan, 0) as promo_gratis_potongan,
            COALESCE(t.promo_gratis_qty, 0) as promo_gratis_qty,
            COALESCE(t.promo_gratis_keterangan, "") as promo_gratis_keterangan,
            ' . $total_akhir_expr . ' as total_akhir,
            ' . $total_akhir_expr . ' as total_harga
        ', false);
        $this->db->from('transaksi t');
        $this->db->join('m_pelanggan p', 't.id_pelanggan = p.id');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = t.id', 'left', false);
        $this->db->where('DATE(t.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(t.tgl_masuk) <=', $tgl_akhir);
        $this->db->order_by('t.id', 'DESC');

        return $this->db->get()->result();
    }

    public function get_laporan($tgl_awal, $tgl_akhir, $jenis_laporan = 'omset', $status_bayar = 'semua')
    {
        $total_akhir_expr = $this->get_total_akhir_expr('detail_aktif.subtotal_normal', 'transaksi.reward_potongan', 'transaksi.promo_gratis_potongan');

        $this->db->select('
            transaksi.*,
            m_pelanggan.nama as nama_pelanggan,
            m_metode_bayar.nama as nama_metode_bayar,
            COALESCE(detail_aktif.subtotal_normal, 0) as subtotal_normal,
            COALESCE(transaksi.reward_potongan, 0) as reward_potongan,
            COALESCE(transaksi.reward_gratis_qty, 0) as reward_gratis_qty,
            COALESCE(transaksi.promo_gratis_potongan, 0) as promo_gratis_potongan,
            COALESCE(transaksi.promo_gratis_qty, 0) as promo_gratis_qty,
            COALESCE(transaksi.promo_gratis_keterangan, "") as promo_gratis_keterangan,
            ' . $total_akhir_expr . ' as total_akhir,
            ' . $total_akhir_expr . ' as total_harga
        ', false);
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('m_metode_bayar', 'm_metode_bayar.id = transaksi.id_metode_bayar', 'left');
        $this->db->join($this->get_subquery_total_detail_aktif(), 'detail_aktif.id_transaksi = transaksi.id', 'left', false);

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

