<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_omset extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (empty($this->session->userdata('role'))) {
            redirect('auth/login');
        }
    }

    public function index()
    {
        // Ambil data omset 12 bulan terakhir
        $data_bulanan = [];

        for ($i = 11; $i >= 0; $i--) {
            $bulan = date('Y-m', strtotime("-$i months"));
            $tahun = date('Y', strtotime("-$i months"));
            $bln   = date('m', strtotime("-$i months"));

            // Hitung total omset bulan ini
            $this->db->select('SUM(transaksi_detail.qty * transaksi_detail.harga) as total_omset');
            $this->db->from('transaksi');
            $this->db->join('transaksi_detail', 'transaksi_detail.id_transaksi = transaksi.id');
            $this->db->where('COALESCE(transaksi_detail.batal, 0) = 0', null, false);
            $this->db->where('YEAR(transaksi.tgl_masuk)', $tahun);
            $this->db->where('MONTH(transaksi.tgl_masuk)', $bln);
            $this->db->where('transaksi.dibayar', 'Sudah Dibayar');
            $this->db->where('transaksi.tgl_bayar IS NOT NULL', null, false);
            $result = $this->db->get()->row();

            // Hitung jumlah transaksi bulan ini
            $this->db->where('YEAR(tgl_masuk)', $tahun);
            $this->db->where('MONTH(tgl_masuk)', $bln);
            $this->db->where('dibayar', 'Sudah Dibayar');
            $this->db->where('tgl_bayar IS NOT NULL', null, false);
            $jml_transaksi = $this->db->count_all_results('transaksi');

            $data_bulanan[] = [
                'bulan'       => $bulan,
                'label'       => $this->_nama_bulan($bln) . ' ' . $tahun,
                'label_short' => $this->_nama_bulan_short($bln),
                'total_omset' => $result->total_omset ? $result->total_omset : 0,
                'jml_transaksi' => $jml_transaksi
            ];
        }

        // Hitung persentase perubahan per bulan
        for ($i = 0; $i < count($data_bulanan); $i++) {
            if ($i == 0) {
                $data_bulanan[$i]['persentase'] = 0;
                $data_bulanan[$i]['trend'] = 'neutral';
            } else {
                $prev = $data_bulanan[$i - 1]['total_omset'];
                $curr = $data_bulanan[$i]['total_omset'];

                if ($prev > 0) {
                    $persen = (($curr - $prev) / $prev) * 100;
                } else {
                    $persen = ($curr > 0) ? 100 : 0;
                }

                $data_bulanan[$i]['persentase'] = round($persen, 1);
                $data_bulanan[$i]['trend'] = ($persen > 0) ? 'up' : (($persen < 0) ? 'down' : 'neutral');
            }
        }

        $data['data_bulanan'] = $data_bulanan;

        // Data bulan ini & bulan lalu untuk summary card
        $data['bulan_ini']  = $data_bulanan[11];
        $data['bulan_lalu'] = $data_bulanan[10];

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('dashboard_omset/index', $data);
        $this->load->view('templates/footer');
    }

    // Helper: Nama bulan Indonesia (full)
    private function _nama_bulan($bln)
    {
        $nama = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        return $nama[$bln] ?? $bln;
    }

    // Helper: Nama bulan Indonesia (short)
    private function _nama_bulan_short($bln)
    {
        $nama = [
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'Mei',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Agu',
            '09' => 'Sep',
            '10' => 'Okt',
            '11' => 'Nov',
            '12' => 'Des'
        ];
        return $nama[$bln] ?? $bln;
    }
}
