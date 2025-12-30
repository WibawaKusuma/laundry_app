<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Karyawan_model extends CI_Model
{

    public function get_all()
    {
        return $this->db->get('users')->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('users')->row();
    }

    public function insert($data)
    {
        $this->db->insert('users', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('users', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('users');
    }
}
