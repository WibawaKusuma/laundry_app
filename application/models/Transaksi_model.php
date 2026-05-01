<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi_model extends CI_Model
{
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
        $this->db->select('SUM(transaksi_detail.qty * transaksi_detail.harga) as total_nilai');
        $this->db->from('transaksi');
        $this->db->join('transaksi_detail', 'transaksi_detail.id_transaksi = transaksi.id');
        $this->db->where('COALESCE(transaksi_detail.batal, 0) = 0', null, false);
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
    }

    public function sum_kas_masuk($tgl_awal, $tgl_akhir)
    {
        $this->db->select('SUM(transaksi_detail.qty * transaksi_detail.harga) as total_nilai');
        $this->db->from('transaksi');
        $this->db->join('transaksi_detail', 'transaksi_detail.id_transaksi = transaksi.id');
        $this->db->where('COALESCE(transaksi_detail.batal, 0) = 0', null, false);
        $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
        $this->db->where('transaksi.tgl_bayar IS NOT NULL', null, false);
        $this->db->where('DATE(transaksi.tgl_bayar) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_bayar) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
    }

    public function sum_piutang($tgl_awal, $tgl_akhir)
    {
        $this->db->select('SUM(transaksi_detail.qty * transaksi_detail.harga) as total_nilai');
        $this->db->from('transaksi');
        $this->db->join('transaksi_detail', 'transaksi_detail.id_transaksi = transaksi.id');
        $this->db->where('COALESCE(transaksi_detail.batal, 0) = 0', null, false);
        $this->db->where('transaksi.dibayar', 'Belum Dibayar');
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
        $result = $this->db->get()->row();

        return (float) ($result->total_nilai ?? 0);
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
        $this->db->select('t.*, p.nama as nama_pelanggan');
        $this->db->from('transaksi t');
        $this->db->join('m_pelanggan p', 't.id_pelanggan = p.id');
        $this->db->where('DATE(t.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(t.tgl_masuk) <=', $tgl_akhir);
        $this->db->order_by('t.id', 'DESC');

        $subquery = $this->db->query('SELECT SUM(dt.qty * dt.harga) AS grand_total, dt.id_transaksi 
                                    FROM transaksi_detail dt 
                                    WHERE COALESCE(dt.batal, 0) = 0
                                    GROUP BY dt.id_transaksi');
        $results = $subquery->result();

        $grand_total = array();
        foreach ($results as $row) {
            $grand_total[$row->id_transaksi] = $row->grand_total;
        }

        $query = $this->db->get();
        $results = $query->result();

        foreach ($results as $row) {
            $row->total_harga = isset($grand_total[$row->id]) ? $grand_total[$row->id] : 0;
        }

        return $results;
    }

    public function get_laporan($tgl_awal, $tgl_akhir, $jenis_laporan = 'omset', $status_bayar = 'semua')
    {
        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan, m_metode_bayar.nama as nama_metode_bayar');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->join('m_metode_bayar', 'm_metode_bayar.id = transaksi.id_metode_bayar', 'left');

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

        $transaksi = $this->db->get()->result();

        foreach ($transaksi as $tr) {
            $this->db->select('SUM(transaksi_detail.qty * transaksi_detail.harga) as total_harga');
            $this->db->from('transaksi_detail');
            $this->db->where('transaksi_detail.id_transaksi', $tr->id);
            $this->db->where('COALESCE(transaksi_detail.batal, 0) = 0', null, false);
            $query = $this->db->get()->row();

            $tr->total_harga = $query->total_harga;
        }

        return $transaksi;
    }
}
