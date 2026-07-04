<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Paket_model extends CI_Model
{
    public function get_all_with_master($keyword = '')
    {
        $keyword = trim((string) $keyword);

        $this->db->select('m_paket_laundry.*, m_kategori.nama_kategori, m_tipe.nama_tipe, m_satuan.nama_satuan');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');

        if ($keyword !== '') {
            $this->db->group_start();
            $this->db->like('m_paket_laundry.nama_paket', $keyword);
            $this->db->or_like('m_tipe.nama_tipe', $keyword);
            $this->db->or_like('m_kategori.nama_kategori', $keyword);
            $this->db->or_like('m_satuan.nama_satuan', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('m_paket_laundry.id_paket_laundry', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('m_paket_laundry', ['id_paket_laundry' => $id])->row();
    }

    public function get_kategori()
    {
        return $this->db->get('m_kategori')->result();
    }

    public function get_tipe()
    {
        return $this->db->get('m_tipe')->result();
    }

    public function get_satuan()
    {
        return $this->db->get('m_satuan')->result();
    }

    public function insert($data)
    {
        return $this->db->insert('m_paket_laundry', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id_paket_laundry', $id);
        return $this->db->update('m_paket_laundry', $data);
    }

    public function delete($id)
    {
        $this->db->where('id_paket_laundry', $id);
        return $this->db->delete('m_paket_laundry');
    }
}
