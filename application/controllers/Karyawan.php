<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Karyawan extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Karyawan_model');
    }


    public function index()
    {
        $data['karyawan'] = $this->Karyawan_model->get_all();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('karyawan/index', $data);
        $this->load->view('templates/footer');
    }

    public function tambah()
    {
        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('karyawan/form');
        $this->load->view('templates/footer');
    }

    public function simpan()
    {
        $name     = $this->input->post('name');
        $username = $this->input->post('username'); // Ambil username
        $password = $this->input->post('password');
        $role     = $this->input->post('role');

        // --- 1. CEK DUPLIKASI USERNAME ---
        $cek = $this->db->get_where('m_users', ['username' => $username])->num_rows();

        if ($cek > 0) {
            // Jika hasil > 0, berarti Username SUDAH ADA.
            // Kita stop proses, kirim pesan error, dan balikan ke form tambah.
            // $this->session->set_flashdata('error', 'Gagal Simpan! Username <b>' . $username . '</b> sudah digunakan. Silakan cari username lain.');
            $this->session->set_flashdata('error', 'Gagal Simpan Data! <br> Data sudah digunakan. Silakan gunakan lain.');
            redirect('karyawan/tambah');
            return; // Stop script agar tidak lanjut ke insert di bawah
        }

        // --- 2. JIKA AMAN (TIDAK ADA DUPLIKAT), BARU SIMPAN ---
        $data = array(
            'name'     => $name,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role'     => $role
        );

        $this->Karyawan_model->insert($data);
        $this->session->set_flashdata('success', 'Data Karyawan berhasil ditambahkan!');
        redirect('karyawan');
    }

    public function edit($id)
    {
        $data['title'] = 'Edit data karyawan';

        $data['karyawan'] = $this->Karyawan_model->get_by_id($id);

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('karyawan/edit', $data);
        $this->load->view('templates/footer');
    }

    public function update()
    {
        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $role = $this->input->post('role');

        $data = array(
            'name' => $name,
            'username' => $username,
            'role' => $role
        );

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->Karyawan_model->update($id, $data);

        // TAMBAHAN: Flashdata Sukses untuk Edit
        $this->session->set_flashdata('success', 'Data Karyawan berhasil diperbarui!');

        redirect('karyawan');
    }

    public function hapus($id)
    {
        $this->Karyawan_model->delete($id);

        // TAMBAHAN: Flashdata Sukses untuk Hapus
        $this->session->set_flashdata('success', 'Data Karyawan berhasil dihapus!');

        redirect('karyawan');
    }
}
