<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Kita extends MY_Controller agar session library otomatis load
class Pelanggan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');

        // CEK LOGIN: Admin & Staff boleh masuk
        // Kita cek apakah role kosong? Jika kosong, tendang ke login
        if (empty($this->session->userdata('role'))) {
            redirect('auth/login');
        }
    }

    public function index()
    {
        $data['pelanggan'] = $this->db->get('pelanggan')->result();


        $this->load->view('templates/header');   // 1. Header (Navbar)
        $this->load->view('templates/sidebar');  // 2. Sidebar (Menu Kiri)
        $this->load->view('pelanggan/index', $data);
        $this->load->view('templates/footer');   // 4. Footer (Script JS)
    }

    public function tambah()
    {
        $data['title'] = 'Tambah Pelanggan';
        // Kirim object kosong agar form tidak error saat memanggil properti
        $data['pelanggan'] = (object)[
            'id' => '',
            'nama' => '',
            'no_hp' => '',
            'alamat' => ''
        ];

        $this->load->view('templates/header');   // 1. Header (Navbar)
        $this->load->view('templates/sidebar');  // 2. Sidebar (Menu Kiri)
        $this->load->view('pelanggan/form', $data);
        $this->load->view('templates/footer');
    }

    public function simpan()
    {
        // Validasi
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('no_hp', 'No HP', 'required|numeric|is_unique[pelanggan.no_hp]', [
            'is_unique' => 'Nomor HP ini sudah terdaftar!'
        ]);
        $this->form_validation->set_rules('alamat', 'Alamat', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            // Jika gagal, kembalikan ke form tambah
            $this->tambah();
        } else {
            $data = array(
                'nama' => $this->input->post('nama', true),
                'no_hp' => $this->input->post('no_hp', true),
                'alamat' => $this->input->post('alamat', true)
            );

            $this->db->insert('pelanggan', $data);
            $this->session->set_flashdata('success', 'Data Pelanggan Berhasil Disimpan');
            redirect('pelanggan');
        }
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Pelanggan';
        $data['pelanggan'] = $this->db->get_where('pelanggan', array('id' => $id))->row();

        $this->load->view('templates/header');   // 1. Header (Navbar)
        $this->load->view('templates/sidebar');  // 2. Sidebar (Menu Kiri)
        $this->load->view('pelanggan/form', $data);
        $this->load->view('templates/footer');
    }

    public function update()
    {
        $id = $this->input->post('id');

        // Validasi (Tanpa is_unique agar tidak error jika nomor tidak diganti)
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('no_hp', 'No HP', 'required|numeric');
        $this->form_validation->set_rules('alamat', 'Alamat', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $data = array(
                'nama' => $this->input->post('nama', true),
                'no_hp' => $this->input->post('no_hp', true),
                'alamat' => $this->input->post('alamat', true)
            );

            $this->db->where('id', $id);
            $this->db->update('pelanggan', $data);
            $this->session->set_flashdata('success', 'Data Pelanggan Berhasil Diupdate');
            redirect('pelanggan');
        }
    }

    public function hapus($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('pelanggan');
        $this->session->set_flashdata('success', 'Data Pelanggan Berhasil Dihapus');
        redirect('pelanggan');
    }
}
