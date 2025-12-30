<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi_model extends CI_Model
{

    public function count_by_status($status)
    {
        $this->db->where('status', $status);
        return $this->db->count_all_results('transaksi');
    }

    public function get_terbaru()
    {
        $this->db->select('t.*, p.nama');
        $this->db->from('transaksi t');
        $this->db->join('pelanggan p', 't.id_pelanggan = p.id');
        $this->db->order_by('t.id', 'DESC');
        $this->db->limit(5);
        return $this->db->get()->result();
    }

    public function get_by_tanggal($tgl_awal, $tgl_akhir)
    {
        $this->db->select('t.*, p.nama_pelanggan');
        $this->db->from('transaksi t');
        $this->db->join('pelanggan p', 't.id_pelanggan = p.id_pelanggan');
        $this->db->where('t.tanggal >=', $tgl_awal);
        $this->db->where('t.tanggal <=', $tgl_akhir);
        $this->db->order_by('t.id_transaksi', 'DESC');

        $subquery = $this->db->query('SELECT SUM(dt.qty * pl.harga) AS grand_total, dt.id_transaksi 
                                    FROM detail_transaksi dt 
                                    JOIN paket_laundry pl ON dt.id_paket = pl.id_paket 
                                    GROUP BY dt.id_transaksi');
        $results = $subquery->result();

        $grand_total = array();
        foreach ($results as $row) {
            $grand_total[$row->id_transaksi] = $row->grand_total;
        }

        $query = $this->db->get();
        $results = $query->result();

        foreach ($results as $row) {
            $row->total_harga = $grand_total[$row->id_transaksi];
        }

        return $results;
    }

    public function get_laporan($tgl_awal, $tgl_akhir)
    {
        // 1. Ambil Data Transaksi Header
        $this->db->select('transaksi.*, pelanggan.nama as nama_pelanggan');
        $this->db->from('transaksi');
        $this->db->join('pelanggan', 'pelanggan.id = transaksi.id_pelanggan');
        // Perhatikan: nama kolom di DB kita 'tgl_masuk', bukan 'tanggal'
        $this->db->where('DATE(transaksi.tgl_masuk) >=', $tgl_awal);
        $this->db->where('DATE(transaksi.tgl_masuk) <=', $tgl_akhir);
        $this->db->order_by('transaksi.id', 'ASC');
        $transaksi = $this->db->get()->result();

        // 2. Hitung Total Harga per Transaksi
        // Kita loop hasil transaksi untuk mencari total harganya
        foreach ($transaksi as $tr) {
            // Query hitung (qty * harga) dari tabel detail & paket
            $this->db->select('SUM(detail_transaksi.qty * paket_laundry.harga) as total_harga');
            $this->db->from('detail_transaksi');
            $this->db->join('paket_laundry', 'paket_laundry.id = detail_transaksi.id_paket');
            $this->db->where('detail_transaksi.id_transaksi', $tr->id);
            $query = $this->db->get()->row();

            // Masukkan hasil hitungan ke object transaksi
            $tr->total_harga = $query->total_harga;
        }

        return $transaksi;
    }
}
