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

    public function get_all_ordered()
    {
        $this->db->order_by('aktif', 'DESC');
        $this->db->order_by('nama', 'ASC');
        return $this->db->get('m_pelanggan')->result();
    }

    public function search($keyword)
    {
        if ($keyword) {
            $this->db->group_start();
            $this->db->like('nama', $keyword);
            $this->db->or_like('no_hp', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('aktif', 'DESC');
        $this->db->order_by('nama', 'ASC');
        return $this->db->get('m_pelanggan')->result();
    }

    public function name_exists($normalized_name, $exclude_id = null)
    {
        $sql = 'SELECT COUNT(*) AS total FROM m_pelanggan WHERE LOWER(TRIM(nama)) = ?';
        $params = [$normalized_name];

        if (!empty($exclude_id)) {
            $sql .= ' AND id != ?';
            $params[] = (int) $exclude_id;
        }

        $row = $this->db->query($sql, $params)->row();
        return !empty($row) && (int) $row->total > 0;
    }

    public function phone_exists($phone, $exclude_id = null)
    {
        $this->db->from('m_pelanggan');
        $this->db->where('no_hp', $phone);

        if (!empty($exclude_id)) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results() > 0;
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
