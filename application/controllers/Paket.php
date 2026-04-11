<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Paket extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        // Load Library Form Validation (Wajib)
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->db->select('m_paket_laundry.*, m_kategori.nama_kategori, m_satuan.nama_satuan');
        $this->db->from('m_paket_laundry');
        $this->db->join('m_kategori', 'm_kategori.id_kategori = m_paket_laundry.id_kategori', 'left');
        $this->db->join('m_satuan', 'm_satuan.id_satuan = m_paket_laundry.id_satuan', 'left');
        $data['paket'] = $this->db->get()->result();
        // --- PERUBAHAN DI SINI ---
        // Kita memuat struktur halaman secara berurutan dari Controller
        $this->load->view('templates/header');   // 1. Header (Navbar)
        $this->load->view('templates/sidebar');  // 2. Sidebar (Menu Kiri)
        $this->load->view('paket/index', $data); // 3. Konten Utama (Tabel)
        $this->load->view('templates/footer');   // 4. Footer (Script JS)
    }

    public function tambah()
    {
        // KITA TAMBAHKAN INI:
        $data['title'] = 'Tambah Paket Laundry';
        // Agar variable $title di form.php tidak error
        $data['paket'] = (object)[
            'id_paket_laundry' => '',
            'id_kategori' => '',
            'nama_paket' => '',
            'id_satuan' => '',
            'durasi_jam' => '',
            'harga' => ''
        ];

        // Ambil data untuk opsi dropdown master
        $data['kategori'] = $this->db->get('m_kategori')->result();
        $data['satuan'] = $this->db->get('m_satuan')->result();

        // --- DI SINI TIDAK ADA SIDEBAR ---
        $this->load->view('templates/header');
        // (Baris sidebar dihapus/tidak ditulis)
        $this->load->view('paket/form', $data); // Form langsung tampil full lebar
        $this->load->view('templates/footer');
    }

    public function simpan()
    {
        // 1. Buat Aturan Validasi (Satpam)
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
        $this->form_validation->set_rules('id_satuan', 'Satuan', 'required', [
            'required' => 'Silakan pilih %s layanan!'
        ]);
        $this->form_validation->set_rules('durasi_jam', 'Durasi', 'required|numeric', [
            'required' => '%s pengerjaan harus diisi!'
        ]);

        // 2. Cek Apakah Lolos Validasi?
        if ($this->form_validation->run() == FALSE) {
            // JIKA GAGAL: Kembalikan ke halaman tambah + Bawa Pesan Error
            $this->tambah();
        } else {
            // JIKA SUKSES: Lanjut simpan ke database
            $data = [
                'id_kategori' => $this->input->post('id_kategori', true),
                'nama_paket' => $this->input->post('nama_paket', true),
                'id_satuan' => $this->input->post('id_satuan', true),
                'durasi_jam' => $this->input->post('durasi_jam', true),
                'harga'      => $this->input->post('harga', true)
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

        // Ambil data untuk opsi dropdown master
        $data['kategori'] = $this->db->get('m_kategori')->result();
        $data['satuan'] = $this->db->get('m_satuan')->result();

        $this->load->view('templates/header');
        $this->load->view('paket/form', $data);
        $this->load->view('templates/footer');
    }

    public function update()
    {
        $id = $this->input->post('id');

        // Aturan validasi sama seperti simpan
        $this->form_validation->set_rules('nama_paket', 'Nama Paket', 'required|trim');
        $this->form_validation->set_rules('harga', 'Harga', 'required|numeric');
        $this->form_validation->set_rules('id_kategori', 'Kategori', 'required');
        $this->form_validation->set_rules('id_satuan', 'Satuan', 'required');
        $this->form_validation->set_rules('durasi_jam', 'Durasi', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            // Jika Gagal, kembalikan ke fungsi edit (bukan tambah)
            $this->edit($id);
        } else {
            $data = [
                'id_kategori' => $this->input->post('id_kategori', true),
                'nama_paket' => $this->input->post('nama_paket', true),
                'id_satuan' => $this->input->post('id_satuan', true),
                'durasi_jam' => $this->input->post('durasi_jam', true),
                'harga'      => $this->input->post('harga', true)
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
