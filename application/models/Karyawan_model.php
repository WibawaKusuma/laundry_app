<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Karyawan_model extends CI_Model
{

    public function get_all()
    {
        return $this->db->get('m_users')->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('m_users')->row();
    }

    public function insert($data)
    {
        $this->db->insert('m_users', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('m_users', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('m_users');
    }
}
