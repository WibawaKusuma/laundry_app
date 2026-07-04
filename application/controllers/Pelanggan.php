<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pelanggan extends MY_Controller
{
    private $placeholder_phone = '99999999999';

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Pelanggan_model');

        if (empty($this->session->userdata('role'))) {
            redirect('auth/login');
        }
    }

    private function normalize_customer_name($name)
    {
        $name = trim((string) $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return $name;
    }

    private function normalize_phone_number($phone)
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return $this->placeholder_phone;
        }

        return $phone;
    }

    private function is_duplicate_name($name, $exclude_id = null)
    {
        $normalized_name = mb_strtolower($this->normalize_customer_name($name), 'UTF-8');
        return $this->Pelanggan_model->name_exists($normalized_name, $exclude_id);
    }

    private function is_duplicate_phone($phone, $exclude_id = null)
    {
        $raw_phone = trim((string) $phone);

        if ($raw_phone === '' || $raw_phone === $this->placeholder_phone) {
            return false;
        }

        $phone = $this->normalize_phone_number($phone);

        if ($phone === null || $phone === '' || $phone === $this->placeholder_phone) {
            return false;
        }

        return $this->Pelanggan_model->phone_exists($phone, $exclude_id);
    }

    public function unique_nama_check($nama, $id = null)
    {
        if ($this->is_duplicate_name($nama, $id)) {
            $this->form_validation->set_message('unique_nama_check', 'Nama pelanggan sudah terdaftar.');
            return false;
        }

        return true;
    }

    public function unique_no_hp_check($no_hp, $id = null)
    {
        if ($this->is_duplicate_phone($no_hp, $id)) {
            $this->form_validation->set_message('unique_no_hp_check', 'Nomor HP ini sudah terdaftar!');
            return false;
        }

        return true;
    }

    public function index()
    {
        $data['pelanggan'] = $this->Pelanggan_model->get_all_ordered();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('pelanggan/index', $data);
        $this->load->view('templates/footer');
    }

    public function search()
    {
        $keyword = $this->input->get('keyword');
        $result = $this->Pelanggan_model->search($keyword);

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
            'alamat' => '',
            'aktif' => 1
        ];

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('pelanggan/form', $data);
        $this->load->view('templates/footer');
    }

    public function simpan()
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim|callback_unique_nama_check');
        $this->form_validation->set_rules('no_hp', 'No HP', 'numeric|callback_unique_no_hp_check');
        $this->form_validation->set_rules('alamat', 'Alamat', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->tambah();
        } else {
            $data = array(
                'nama' => $this->normalize_customer_name($this->input->post('nama', true)),
                'no_hp' => $this->normalize_phone_number($this->input->post('no_hp', true)),
                'alamat' => $this->input->post('alamat', true),
                'aktif' => (int) $this->input->post('aktif')
            );

            $this->Pelanggan_model->insert($data);
            $this->session->set_flashdata('success', 'Data Pelanggan Berhasil Disimpan');
            redirect('pelanggan');
        }
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Pelanggan';
        $data['pelanggan'] = $this->Pelanggan_model->get_by_id($id);

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('pelanggan/form', $data);
        $this->load->view('templates/footer');
    }

    public function update()
    {
        $id = $this->input->post('id');

        $this->form_validation->set_rules('nama', 'Nama', 'required|trim|callback_unique_nama_check[' . $id . ']');
        $this->form_validation->set_rules('no_hp', 'No HP', 'numeric|callback_unique_no_hp_check[' . $id . ']');
        $this->form_validation->set_rules('alamat', 'Alamat', 'required|trim');
        $this->form_validation->set_rules('aktif', 'Status pelanggan', 'required|in_list[0,1]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $data = array(
                'nama' => $this->normalize_customer_name($this->input->post('nama', true)),
                'no_hp' => $this->normalize_phone_number($this->input->post('no_hp', true)),
                'alamat' => $this->input->post('alamat', true),
                'aktif' => (int) $this->input->post('aktif')
            );

            $this->Pelanggan_model->update($id, $data);
            $this->session->set_flashdata('success', 'Data Pelanggan Berhasil Diupdate');
            redirect('pelanggan');
        }
    }

    public function hapus($id)
    {
        $this->Pelanggan_model->delete($id);
        $this->session->set_flashdata('success', 'Data Pelanggan Berhasil Dihapus');
        redirect('pelanggan');
    }
}
