<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laporan extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Admin_Controller otomatis cek login, jadi tidak perlu if session lagi disini
        $this->load->model('Transaksi_model');
    }

    public function index()
    {
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        // Default tanggal: Awal bulan ini s/d Hari ini
        if (empty($tgl_awal)) {
            // $tgl_awal = date('Y-m-01');
            $tgl_awal = date('Y-m-d');
        }
        if (empty($tgl_akhir)) {
            $tgl_akhir = date('Y-m-d');
        }

        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;
        $data['laporan']   = $this->Transaksi_model->get_laporan($tgl_awal, $tgl_akhir);

        // Load View dengan Layout Header/Sidebar/Footer yang benar
        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('laporan/index', $data);
        $this->load->view('templates/footer');
    }

    // --- FUNGSI BARU UNTUK CETAK PDF/A4 ---
    public function cetak()
    {
        // Ambil filter tanggal dari URL
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        // Validasi jika user main tembak URL tanpa tanggal
        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal = date('Y-m-01');
            $tgl_akhir = date('Y-m-d');
        }

        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;
        $data['laporan']   = $this->Transaksi_model->get_laporan($tgl_awal, $tgl_akhir);

        // Load View Khusus Cetak (Tanpa Header/Sidebar Admin)
        $data['company'] = $this->company;
        $this->load->view('laporan/cetak', $data);
    }

    // --- FUNGSI EXPORT KE EXCEL ---
    public function excel()
    {
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal = date('Y-m-01');
            $tgl_akhir = date('Y-m-d');
        }

        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;
        $data['laporan']   = $this->Transaksi_model->get_laporan($tgl_awal, $tgl_akhir);

        // Load View Khusus Excel
        $this->load->view('laporan/excel', $data);
    }
}
