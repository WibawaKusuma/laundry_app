<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('status') != 'login') {
            // Jika belum login atau session habis, tendang ke halaman login
            redirect('auth');
        }

        $this->load->model('Pelanggan_model');
        $this->load->model('Transaksi_model');
    }

    public function index()
    {
        // 1. Ambil Filter Tanggal dari URL (GET)
        $tgl_awal = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        // 2. Set Default (Jika kosong, tampilkan HANYA HARI INI)
        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal  = date('Y-m-d'); // <-- Ubah ini jadi hari ini
            $tgl_akhir = date('Y-m-d'); // Hari ini
        }

        // 3. Panggil Model dengan Parameter Tanggal
        $total_pelanggan   = $this->Pelanggan_model->count_all_results();
        $transaksi_baru    = $this->Transaksi_model->count_by_status('Baru', $tgl_awal, $tgl_akhir);
        $transaksi_proses  = $this->Transaksi_model->count_by_status('Proses', $tgl_awal, $tgl_akhir);
        $transaksi_selesai = $this->Transaksi_model->count_by_status('Selesai', $tgl_awal, $tgl_akhir);
        $terbaru           = $this->Transaksi_model->get_terbaru($tgl_awal, $tgl_akhir);

        $data = array(
            'total_pelanggan'   => $total_pelanggan,
            'transaksi_baru'    => $transaksi_baru,
            'transaksi_proses'  => $transaksi_proses,
            'transaksi_selesai' => $transaksi_selesai,
            'terbaru'           => $terbaru,
            'tgl_awal'          => $tgl_awal,
            'tgl_akhir'         => $tgl_akhir
        );

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
}
