<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Paket extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        $allowed_roles = ['admin', 'kasir'];
        if (!in_array($this->session->userdata('role'), $allowed_roles, true)) {
            redirect('auth/login');
        }

        $this->preventPageCache();
        $this->load->library('form_validation');
        $this->load->model('Paket_model');
    }

    public function index()
    {
        $keyword = trim((string) $this->input->get('q', true));
        $data['paket'] = $this->Paket_model->get_all_with_master($keyword);
        $data['keyword'] = $keyword;

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('paket/index', $data);
        $this->load->view('templates/footer');
    }

    public function tambah()
    {
        $data['title'] = 'Tambah Paket Laundry';
        $data['paket'] = (object) [
            'id_paket_laundry' => '',
            'id_kategori' => '',
            'id_tipe' => '',
            'nama_paket' => '',
            'id_satuan' => '',
            'durasi_jam' => '',
            'harga' => ''
        ];

        $data['kategori'] = $this->Paket_model->get_kategori();
        $data['tipe'] = $this->Paket_model->get_tipe();
        $data['satuan'] = $this->Paket_model->get_satuan();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('paket/form', $data);
        $this->load->view('templates/footer');
    }

    public function simpan()
    {
        $this->form_validation->set_rules('nama_paket', 'Nama Paket', 'required|trim', [
            'required' => '%s tidak boleh kosong!'
        ]);
        $this->form_validation->set_rules('harga', 'Harga', 'required|numeric', [
            'required' => '%s harus diisi!',
            'numeric' => '%s harus berupa angka!'
        ]);
        $this->form_validation->set_rules('id_kategori', 'Kategori', 'required', [
            'required' => 'Silakan pilih %s layanan!'
        ]);
        $this->form_validation->set_rules('id_tipe', 'Tipe', 'required', [
            'required' => 'Silakan pilih %s laundry!'
        ]);
        $this->form_validation->set_rules('id_satuan', 'Satuan', 'required', [
            'required' => 'Silakan pilih %s layanan!'
        ]);
        $this->form_validation->set_rules('durasi_jam', 'Durasi', 'required|numeric', [
            'required' => '%s pengerjaan harus diisi!'
        ]);

        if ($this->form_validation->run() == FALSE) {
            $this->tambah();
        } else {
            $data = [
                'id_kategori' => $this->input->post('id_kategori', true),
                'id_tipe' => $this->input->post('id_tipe', true),
                'nama_paket' => $this->input->post('nama_paket', true),
                'id_satuan' => $this->input->post('id_satuan', true),
                'durasi_jam' => $this->input->post('durasi_jam', true),
                'harga' => $this->input->post('harga', true)
            ];

            if ($this->Paket_model->insert($data)) {
                $this->session->set_flashdata('success', 'Data Laundry Berhasil Disimpan');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan ke database!');
            }

            redirect('paket');
        }
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Paket Laundry';
        $data['paket'] = $this->Paket_model->get_by_id($id);

        $data['kategori'] = $this->Paket_model->get_kategori();
        $data['tipe'] = $this->Paket_model->get_tipe();
        $data['satuan'] = $this->Paket_model->get_satuan();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('paket/form', $data);
        $this->load->view('templates/footer');
    }

    public function update()
    {
        $id = $this->input->post('id');

        $this->form_validation->set_rules('nama_paket', 'Nama Paket', 'required|trim');
        $this->form_validation->set_rules('harga', 'Harga', 'required|numeric');
        $this->form_validation->set_rules('id_kategori', 'Kategori', 'required');
        $this->form_validation->set_rules('id_tipe', 'Tipe', 'required');
        $this->form_validation->set_rules('id_satuan', 'Satuan', 'required');
        $this->form_validation->set_rules('durasi_jam', 'Durasi', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $data = [
                'id_kategori' => $this->input->post('id_kategori', true),
                'id_tipe' => $this->input->post('id_tipe', true),
                'nama_paket' => $this->input->post('nama_paket', true),
                'id_satuan' => $this->input->post('id_satuan', true),
                'durasi_jam' => $this->input->post('durasi_jam', true),
                'harga' => $this->input->post('harga', true)
            ];

            if ($this->Paket_model->update($id, $data)) {
                $this->session->set_flashdata('success', 'Data Laundry Berhasil Diupdate');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupdate data!');
            }

            redirect('paket');
        }
    }

    public function hapus($id)
    {
        $this->Paket_model->delete($id);
        $this->session->set_flashdata('flash', 'Dihapus');
        redirect('paket');
    }
}
