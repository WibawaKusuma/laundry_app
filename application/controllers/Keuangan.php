<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Keuangan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Pastikan User sudah login
        // if (!$this->session->userdata('id_user')) { redirect('auth'); }

        $this->load->model('Keuangan_model');
    }

    public function index()
    {
        // 1. Ambil Filter Tanggal
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        // 2. Default ke Bulan Ini jika kosong
        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal  = date('Y-m-d');
            $tgl_akhir = date('Y-m-d');
        }

        $data['title'] = 'Data Pengeluaran';

        // 3. Panggil Model dengan Parameter Tanggal
        $data['pengeluaran'] = $this->Keuangan_model->get_all($tgl_awal, $tgl_akhir);

        // 4. Hitung Total (Otomatis sesuai data yang difilter)
        $data['total_pengeluaran'] = 0;
        foreach ($data['pengeluaran'] as $row) {
            $data['total_pengeluaran'] += $row->nominal;
        }

        // Kirim balik tanggal ke view
        $data['tgl_awal'] = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
        $this->load->view('keuangan/index', $data);
        $this->load->view('templates/footer');
    }

    public function tambah()
    {
        $data['title'] = 'Tambah Pengeluaran';
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
        $this->load->view('keuangan/form');
        $this->load->view('templates/footer');
    }

    public function simpan()
    {
        // AMBIL ID USER DARI SESSION LOGIN
        // Sesuaikan key session dengan sistem login Anda (biasanya 'id_user' atau 'id')
        $id_user_login = $this->session->userdata('id_user');

        // Fallback: Jika session kosong (misal testing), set ke 1
        if (empty($id_user_login)) {
            $id_user_login = 1;
        }

        $data = [
            'tgl_pengeluaran' => $this->input->post('tgl_pengeluaran'),
            'keterangan'      => $this->input->post('keterangan'),
            'nominal'         => $this->input->post('nominal'),
            'catatan'         => $this->input->post('catatan'),
            'id_user'         => $id_user_login // <--- SIMPAN ID USER
        ];

        $this->Keuangan_model->insert($data);
        $this->session->set_flashdata('success', 'Data Pengeluaran berhasil disimpan!');
        redirect('keuangan');
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Pengeluaran';
        $data['pengeluaran'] = $this->Keuangan_model->get_by_id($id);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar');
        $this->load->view('keuangan/form', $data);
        $this->load->view('templates/footer');
    }

    public function update()
    {
        $id = $this->input->post('id');

        // Saat update, id_user TIDAK diubah agar history penginput awal tetap terjaga
        $data = [
            'tgl_pengeluaran' => $this->input->post('tgl_pengeluaran'),
            'keterangan'      => $this->input->post('keterangan'),
            'nominal'         => $this->input->post('nominal'),
            'catatan'         => $this->input->post('catatan'),
        ];

        $this->Keuangan_model->update($id, $data);
        $this->session->set_flashdata('success', 'Data Pengeluaran berhasil diperbarui!');
        redirect('keuangan');
    }

    public function hapus($id)
    {
        $this->Keuangan_model->delete($id);
        $this->session->set_flashdata('success', 'Data Pengeluaran berhasil dihapus!');
        redirect('keuangan');
    }

    public function export_excel()
    {
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal  = date('Y-m-01');
            $tgl_akhir = date('Y-m-d');
        }

        // --- TAMBAHAN PENTING ---
        // Kirim data tanggal ke View agar bisa ditampilkan
        $data['tgl_awal'] = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;
        // ------------------------

        $data['pengeluaran'] = $this->Keuangan_model->get_by_date($tgl_awal, $tgl_akhir);
        $data['title'] = 'Laporan-Pengeluaran-' . $tgl_awal . '-sd-' . $tgl_akhir;

        $this->load->view('keuangan/excel', $data);
    }
}
