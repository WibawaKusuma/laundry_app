<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Config_model extends CI_Model
{
    /**
     * Cache config agar tidak query berulang per request
     */
    private $cache = null;

    /**
     * Ambil semua config sebagai associative array
     * @return array ['company_name' => '...', 'company_phone' => '...', ...]
     */
    public function get_all()
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $query = $this->db->get('config');
        $result = [];

        foreach ($query->result() as $row) {
            $result[$row->config_key] = $row->config_value;
        }

        $this->cache = $result;
        return $result;
    }

    /**
     * Ambil satu config berdasarkan key
     * @param string $key
     * @return string|null
     */
    public function get($key)
    {
        $all = $this->get_all();
        return isset($all[$key]) ? $all[$key] : null;
    }

    /**
     * Update satu config
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        $this->db->where('config_key', $key);
        $this->db->update('config', ['config_value' => $value]);
        $this->cache = null; // Reset cache
    }
}
