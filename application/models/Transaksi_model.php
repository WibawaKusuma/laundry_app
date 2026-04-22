<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi_model extends CI_Model
{

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

        $subquery = $this->db->query('SELECT SUM(dt.qty * pl.harga) AS grand_total, dt.id_transaksi 
                                    FROM transaksi_detail dt 
                                    JOIN m_paket_laundry pl ON dt.id_paket = pl.id_paket_laundry 
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

    public function get_laporan($tgl_awal, $tgl_akhir, $status_bayar = 'semua')
    {
        $this->db->select('transaksi.*, m_pelanggan.nama as nama_pelanggan');
        $this->db->from('transaksi');
        $this->db->join('m_pelanggan', 'm_pelanggan.id = transaksi.id_pelanggan');
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);

        if ($status_bayar === 'lunas') {
            $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
        } elseif ($status_bayar === 'belum') {
            $this->db->where('transaksi.dibayar', 'Belum Dibayar');
        }

        $this->db->order_by('transaksi.id', 'ASC');
        $transaksi = $this->db->get()->result();

        foreach ($transaksi as $tr) {
            $this->db->select('SUM(transaksi_detail.qty * m_paket_laundry.harga) as total_harga');
            $this->db->from('transaksi_detail');
            $this->db->join('m_paket_laundry', 'm_paket_laundry.id_paket_laundry = transaksi_detail.id_paket');
            $this->db->where('transaksi_detail.id_transaksi', $tr->id);
            $query = $this->db->get()->row();

            $tr->total_harga = $query->total_harga;
        }

        return $transaksi;
    }
}
