<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pelanggan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');

        if (empty($this->session->userdata('role'))) {
            redirect('auth/login');
        }
    }

    public function index()
    {
        $data['pelanggan'] = $this->db->get('m_pelanggan')->result();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('pelanggan/index', $data);
        $this->load->view('templates/footer');
    }

    public function search()
    {
        $keyword = $this->input->get('keyword');

        if ($keyword) {
            $this->db->group_start();
            $this->db->like('nama', $keyword);
            $this->db->or_like('no_hp', $keyword);
            $this->db->group_end();
        }

        $result = $this->db->get('m_pelanggan')->result();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    public function tambah()
    {
        $data['title'] = 'Tambah Pelanggan';
        $data['pelanggan'] = (object) [
            'id' => '',
            'nama' => '',
            'no_hp' => '',
            'alamat' => ''
        ];

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('pelanggan/form', $data);
        $this->load->view('templates/footer');
    }

    public function simpan()
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('no_hp', 'No HP', 'required|numeric|is_unique[m_pelanggan.no_hp]', [
            'is_unique' => 'Nomor HP ini sudah terdaftar!'
        ]);
        $this->form_validation->set_rules('alamat', 'Alamat', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->tambah();
        } else {
            $data = array(
                'nama' => $this->input->post('nama', true),
                'no_hp' => $this->input->post('no_hp', true),
                'alamat' => $this->input->post('alamat', true)
            );

            $this->db->insert('m_pelanggan', $data);
            $this->session->set_flashdata('success', 'Data Pelanggan Berhasil Disimpan');
            redirect('pelanggan');
        }
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Pelanggan';
        $data['pelanggan'] = $this->db->get_where('m_pelanggan', array('id' => $id))->row();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('pelanggan/form', $data);
        $this->load->view('templates/footer');
    }

    public function update()
    {
        $id = $this->input->post('id');

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
            $this->db->update('m_pelanggan', $data);
            $this->session->set_flashdata('success', 'Data Pelanggan Berhasil Diupdate');
            redirect('pelanggan');
        }
    }

    public function hapus($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('m_pelanggan');
        $this->session->set_flashdata('success', 'Data Pelanggan Berhasil Dihapus');
        redirect('pelanggan');
    }
}
