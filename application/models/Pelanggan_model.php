<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pelanggan_model extends CI_Model
{

    public function count_all_results()
    {
        return $this->db->count_all_results('m_pelanggan');
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('m_pelanggan')->row();
    }

    public function get_all()
    {
        return $this->db->get('m_pelanggan')->result();
    }

    public function insert($data)
    {
        $this->db->insert('m_pelanggan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('m_pelanggan', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('m_pelanggan');
    }
}
