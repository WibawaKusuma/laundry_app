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
        $data['paket'] = $this->db->get('paket_laundry')->result();
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
            'id' => '',
            'nama_paket' => '',
            'harga' => '',
            'jenis' => '',
            'durasi_jam' => ''
        ];

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
        $this->form_validation->set_rules('jenis', 'Jenis', 'required', [
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
                'nama_paket' => $this->input->post('nama_paket', true),
                'harga'      => $this->input->post('harga', true),
                'jenis'      => $this->input->post('jenis', true),
                'durasi_jam' => $this->input->post('durasi_jam', true)
            ];

            if ($this->db->insert('paket_laundry', $data)) {
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
        $data['paket'] = $this->db->get_where('paket_laundry', array('id' => $id))->row();

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
        $this->form_validation->set_rules('jenis', 'Jenis', 'required');
        $this->form_validation->set_rules('durasi_jam', 'Durasi', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            // Jika Gagal, kembalikan ke fungsi edit (bukan tambah)
            $this->edit($id);
        } else {
            $data = [
                'nama_paket' => $this->input->post('nama_paket', true),
                'harga'      => $this->input->post('harga', true),
                'jenis'      => $this->input->post('jenis', true),
                'durasi_jam' => $this->input->post('durasi_jam', true)
            ];

            $this->db->where('id', $id);
            if ($this->db->update('paket_laundry', $data)) {
                $this->session->set_flashdata('success', 'Data Laundry Berhasil Diupdate');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupdate data!');
            }

            redirect('paket');
        }
    }

    public function hapus($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('paket_laundry');
        $this->session->set_flashdata('flash', 'Dihapus');
        redirect('paket');
    }
}
