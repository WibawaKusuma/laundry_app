<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pelanggan_model extends CI_Model
{

    public function count_all_results()
    {
        return $this->db->count_all_results('pelanggan');
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('pelanggan')->row();
    }

    public function get_all()
    {
        return $this->db->get('pelanggan')->result();
    }

    public function insert($data)
    {
        $this->db->insert('pelanggan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('pelanggan', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('pelanggan');
    }
}
