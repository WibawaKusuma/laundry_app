<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Paket extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }

    public function index()
    {
        $keyword = trim((string) $this->input->get('q', true));

        $this->db->select('m_paket_laundry.*, m_kategori.nama_kategori, m_tipe.nama_tipe, m_satuan.nama_satuan');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_tipe', 'm_tipe.id_tipe = m_paket_laundry.id_tipe', 'left');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        if ($keyword !== '') {
            $this->db->group_start();
            $this->db->like('m_paket_laundry.nama_paket', $keyword);
            $this->db->or_like('m_tipe.nama_tipe', $keyword);
            $this->db->or_like('m_kategori.nama_kategori', $keyword);
            $this->db->or_like('m_satuan.nama_satuan', $keyword);
            $this->db->group_end();
        }
        $this->db->order_by('m_paket_laundry.id_paket_laundry', 'ASC');
        $data['paket'] = $this->db->get()->result();
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

        $data['kategori'] = $this->db->get('m_kategori')->result();
        $data['tipe'] = $this->db->get('m_tipe')->result();
        $data['satuan'] = $this->db->get('m_satuan')->result();

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

            if ($this->db->insert('m_paket_laundry', $data)) {
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
        $data['paket'] = $this->db->get_where('m_paket_laundry', array('id_paket_laundry' => $id))->row();

        $data['kategori'] = $this->db->get('m_kategori')->result();
        $data['tipe'] = $this->db->get('m_tipe')->result();
        $data['satuan'] = $this->db->get('m_satuan')->result();

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

            $this->db->where('id_paket_laundry', $id);
            if ($this->db->update('m_paket_laundry', $data)) {
                $this->session->set_flashdata('success', 'Data Laundry Berhasil Diupdate');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupdate data!');
            }

            redirect('paket');
        }
    }

    public function hapus($id)
    {
        $this->db->where('id_paket_laundry', $id);
        $this->db->delete('m_paket_laundry');
        $this->session->set_flashdata('flash', 'Dihapus');
        redirect('paket');
    }
}
