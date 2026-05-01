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
        $this->load->model('Keuangan_model');
    }

    public function index()
    {
        $is_admin = $this->session->userdata('role') === 'admin';

        // 1. Ambil Filter Tanggal dari URL (GET)
        $tgl_awal = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');

        // 2. Set Default (Jika kosong, tampilkan bulan berjalan)
        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal  = date('Y-m-01');
            $tgl_akhir = date('Y-m-d');
        }

        // 3. Panggil Model dengan Parameter Tanggal
        $transaksi_baru    = $this->Transaksi_model->count_by_status('Baru', $tgl_awal, $tgl_akhir);
        $transaksi_proses  = $this->Transaksi_model->count_by_status('Proses', $tgl_awal, $tgl_akhir);
        $transaksi_selesai = $this->Transaksi_model->count_by_status('Selesai', $tgl_awal, $tgl_akhir);
        $transaksi_belum_lunas = $this->Transaksi_model->count_by_payment_status('Belum Dibayar', $tgl_awal, $tgl_akhir);
        $transaksi_lunas_belum_diambil = $this->Transaksi_model->count_paid_ready_pickup($tgl_awal, $tgl_akhir);
        $terbaru           = $this->Transaksi_model->get_terbaru($tgl_awal, $tgl_akhir);

        $total_pelanggan = 0;
        $omset_periode = 0;
        $kas_masuk_periode = 0;
        $piutang_periode = 0;
        $pengeluaran_periode = 0;
        $saldo_operasional = 0;

        if ($is_admin) {
            $total_pelanggan = $this->Pelanggan_model->count_all_results();
            $omset_periode = $this->Transaksi_model->sum_omset($tgl_awal, $tgl_akhir);
            $kas_masuk_periode = $this->Transaksi_model->sum_kas_masuk($tgl_awal, $tgl_akhir);
            $piutang_periode = $this->Transaksi_model->sum_piutang($tgl_awal, $tgl_akhir);
            $pengeluaran_periode = $this->Keuangan_model->sum_pengeluaran($tgl_awal, $tgl_akhir);
            $saldo_operasional = $kas_masuk_periode - $pengeluaran_periode;
        }

        $data = array(
            'is_admin' => $is_admin,
            'total_pelanggan'   => $total_pelanggan,
            'transaksi_baru'    => $transaksi_baru,
            'transaksi_proses'  => $transaksi_proses,
            'transaksi_selesai' => $transaksi_selesai,
            'transaksi_belum_lunas' => $transaksi_belum_lunas,
            'transaksi_lunas_belum_diambil' => $transaksi_lunas_belum_diambil,
            'omset_periode' => $omset_periode,
            'kas_masuk_periode' => $kas_masuk_periode,
            'piutang_periode' => $piutang_periode,
            'pengeluaran_periode' => $pengeluaran_periode,
            'saldo_operasional' => $saldo_operasional,
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
