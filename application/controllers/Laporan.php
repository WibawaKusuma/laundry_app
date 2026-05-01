<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laporan extends Admin_Controller
{
    private function normalize_report_type($jenis_laporan)
    {
        $allowed = ['omset', 'kas_masuk', 'piutang', 'pengambilan'];
        $jenis_laporan = strtolower(trim((string) $jenis_laporan));

        return in_array($jenis_laporan, $allowed, true) ? $jenis_laporan : 'omset';
    }

    private function normalize_payment_filter($status_bayar)
    {
        $allowed = ['semua', 'lunas', 'belum'];
        $status_bayar = strtolower(trim((string) $status_bayar));

        return in_array($status_bayar, $allowed, true) ? $status_bayar : 'semua';
    }

    private function get_report_meta($jenis_laporan)
    {
        switch ($jenis_laporan) {
            case 'kas_masuk':
                return [
                    'title' => 'Laporan Kas Masuk',
                    'heading' => 'Kas Masuk',
                    'icon' => 'fa-wallet',
                    'date_label' => 'Tanggal Bayar',
                    'summary_label' => 'Grand Total Kas Masuk',
                    'empty_message' => 'Tidak ada kas masuk pada periode ini.',
                    'status_filter_enabled' => false,
                ];

            case 'piutang':
                return [
                    'title' => 'Laporan Piutang',
                    'heading' => 'Piutang',
                    'icon' => 'fa-file-invoice-dollar',
                    'date_label' => 'Tanggal Masuk',
                    'summary_label' => 'Grand Total Piutang',
                    'empty_message' => 'Tidak ada piutang pada periode ini.',
                    'status_filter_enabled' => false,
                ];

            case 'pengambilan':
                return [
                    'title' => 'Laporan Pengambilan',
                    'heading' => 'Pengambilan',
                    'icon' => 'fa-box-open',
                    'date_label' => 'Tanggal Diambil',
                    'summary_label' => 'Grand Total Pengambilan',
                    'empty_message' => 'Tidak ada pengambilan pada periode ini.',
                    'status_filter_enabled' => false,
                ];

            case 'omset':
            default:
                return [
                    'title' => 'Laporan Omset',
                    'heading' => 'Omset',
                    'icon' => 'fa-chart-line',
                    'date_label' => 'Tanggal Masuk',
                    'summary_label' => 'Grand Total Omset',
                    'empty_message' => 'Tidak ada transaksi omset pada periode ini.',
                    'status_filter_enabled' => true,
                ];
        }
    }

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
        $jenis_laporan = $this->normalize_report_type($this->input->get('jenis_laporan'));
        $status_bayar = $this->normalize_payment_filter($this->input->get('status_bayar'));
        $report_meta = $this->get_report_meta($jenis_laporan);

        // Default tanggal: Awal bulan ini s/d Hari ini
        if (empty($tgl_awal)) {
            $tgl_awal = date('Y-m-01');
        }
        if (empty($tgl_akhir)) {
            $tgl_akhir = date('Y-m-d');
        }

        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;
        $data['jenis_laporan'] = $jenis_laporan;
        $data['status_bayar'] = $status_bayar;
        $data['report_meta'] = $report_meta;
        $data['laporan']   = $this->Transaksi_model->get_laporan($tgl_awal, $tgl_akhir, $jenis_laporan, $status_bayar);

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
        $jenis_laporan = $this->normalize_report_type($this->input->get('jenis_laporan'));
        $status_bayar = $this->normalize_payment_filter($this->input->get('status_bayar'));
        $report_meta = $this->get_report_meta($jenis_laporan);

        // Validasi jika user main tembak URL tanpa tanggal
        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal = date('Y-m-01');
            $tgl_akhir = date('Y-m-d');
        }

        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;
        $data['jenis_laporan'] = $jenis_laporan;
        $data['status_bayar'] = $status_bayar;
        $data['report_meta'] = $report_meta;
        $data['laporan']   = $this->Transaksi_model->get_laporan($tgl_awal, $tgl_akhir, $jenis_laporan, $status_bayar);

        // Load View Khusus Cetak (Tanpa Header/Sidebar Admin)
        $data['company'] = $this->company;
        $this->load->view('laporan/cetak', $data);
    }

    // --- FUNGSI EXPORT KE EXCEL ---
    public function excel()
    {
        $tgl_awal  = $this->input->get('tgl_awal');
        $tgl_akhir = $this->input->get('tgl_akhir');
        $jenis_laporan = $this->normalize_report_type($this->input->get('jenis_laporan'));
        $status_bayar = $this->normalize_payment_filter($this->input->get('status_bayar'));
        $report_meta = $this->get_report_meta($jenis_laporan);

        if (empty($tgl_awal) || empty($tgl_akhir)) {
            $tgl_awal = date('Y-m-01');
            $tgl_akhir = date('Y-m-d');
        }

        $data['tgl_awal']  = $tgl_awal;
        $data['tgl_akhir'] = $tgl_akhir;
        $data['jenis_laporan'] = $jenis_laporan;
        $data['status_bayar'] = $status_bayar;
        $data['report_meta'] = $report_meta;
        $data['laporan']   = $this->Transaksi_model->get_laporan($tgl_awal, $tgl_akhir, $jenis_laporan, $status_bayar);

        // Load View Khusus Excel
        $this->load->view('laporan/excel', $data);
    }
}
