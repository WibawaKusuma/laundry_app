<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pelanggan_model');
        $this->load->model('Transaksi_model');
    }

    public function index()
    {
        $total_pelanggan = $this->Pelanggan_model->count_all_results();
        $transaksi_baru = $this->Transaksi_model->count_by_status('Baru');
        $transaksi_proses = $this->Transaksi_model->count_by_status('Proses');
        $transaksi_selesai = $this->Transaksi_model->count_by_status('Selesai');
        $terbaru = $this->Transaksi_model->get_terbaru();

        $data = array(
            'total_pelanggan' => $total_pelanggan,
            'transaksi_baru' => $transaksi_baru,
            'transaksi_proses' => $transaksi_proses,
            'transaksi_selesai' => $transaksi_selesai,
            'terbaru' => $terbaru
        );

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
}
