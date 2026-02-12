<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Keuangan_model extends CI_Model
{
    // Tambahkan parameter default null
    public function get_all($tgl_awal = null, $tgl_akhir = null)
    {
        $this->db->select('pengeluaran.*, users.name as nama_user');
        $this->db->from('pengeluaran');
        $this->db->join('users', 'users.id = pengeluaran.id_user', 'left');

        // --- FILTER TANGGAL ---
        if ($tgl_awal && $tgl_akhir) {
            $this->db->where('DATE(tgl_pengeluaran) >=', $tgl_awal);
            $this->db->where('DATE(tgl_pengeluaran) <=', $tgl_akhir);
        }
        // ----------------------

        $this->db->order_by('tgl_pengeluaran', 'DESC');
        $this->db->order_by('id', 'DESC');

        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('pengeluaran', ['id' => $id])->row();
    }

    public function insert($data)
    {
        $this->db->insert('pengeluaran', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('pengeluaran', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('pengeluaran');
    }

    public function get_by_date($tgl_awal, $tgl_akhir)
    {
        $this->db->select('pengeluaran.*, users.name as nama_user');
        $this->db->from('pengeluaran');
        $this->db->join('users', 'users.id = pengeluaran.id_user', 'left');

        // Filter Tanggal
        // Pastikan format di database DATE (YYYY-MM-DD)
        $this->db->where('tgl_pengeluaran >=', $tgl_awal);
        $this->db->where('tgl_pengeluaran <=', $tgl_akhir);

        $this->db->order_by('tgl_pengeluaran', 'ASC'); // Urutkan dari tanggal lama ke baru

        return $this->db->get()->result();
    }
}
