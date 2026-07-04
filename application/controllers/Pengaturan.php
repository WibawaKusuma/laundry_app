<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengaturan extends Admin_Controller
{
    private $promo_defaults = [
        'promo_opening_aktif' => '0',
        'promo_opening_gratis_qty' => '3',
        'promo_opening_label' => 'Promo Opening Gratis Cuci 3 Kg',
        'promo_daily_aktif' => '1',
        'promo_daily_min_kg' => '7',
        'promo_daily_gratis_qty' => '1',
        'promo_daily_label' => 'Promo Daily Minimal 7 Kg Gratis 1 Kg',
        'promo_sepatu_aktif' => '0',
        'promo_sepatu_min_pasang' => '5',
        'promo_sepatu_gratis_qty' => '1',
        'promo_sepatu_label' => 'Promo Sepatu 5 Pasang Gratis 1 Pasang',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Config_model');
    }

    public function index()
    {
        redirect('pengaturan/promo');
    }

    public function promo()
    {
        $data['promo'] = $this->get_promo_config();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('pengaturan/promo', $data);
        $this->load->view('templates/footer');
    }

    public function update_promo()
    {
        $this->set_promo_validation_rules();

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', trim(str_replace(["\r", "\n"], '', nl2br(validation_errors()))));
            redirect('pengaturan/promo');
            return;
        }

        $items = [
            'promo_opening_aktif' => $this->input->post('promo_opening_aktif') ? '1' : '0',
            'promo_opening_gratis_qty' => $this->normalize_number($this->input->post('promo_opening_gratis_qty')),
            'promo_opening_label' => trim($this->input->post('promo_opening_label', true)),
            'promo_daily_aktif' => $this->input->post('promo_daily_aktif') ? '1' : '0',
            'promo_daily_min_kg' => $this->normalize_number($this->input->post('promo_daily_min_kg')),
            'promo_daily_gratis_qty' => $this->normalize_number($this->input->post('promo_daily_gratis_qty')),
            'promo_daily_label' => trim($this->input->post('promo_daily_label', true)),
            'promo_sepatu_aktif' => $this->input->post('promo_sepatu_aktif') ? '1' : '0',
            'promo_sepatu_min_pasang' => $this->normalize_number($this->input->post('promo_sepatu_min_pasang')),
            'promo_sepatu_gratis_qty' => $this->normalize_number($this->input->post('promo_sepatu_gratis_qty')),
            'promo_sepatu_label' => trim($this->input->post('promo_sepatu_label', true)),
        ];

        if ($items['promo_opening_aktif'] === '1' && $items['promo_daily_aktif'] === '1') {
            $this->session->set_flashdata('error', 'Promo Opening dan Promo Daily tidak boleh aktif bersamaan. Saat masa opening, matikan Promo Daily terlebih dahulu.');
            redirect('pengaturan/promo');
            return;
        }

        $this->Config_model->set_many($items);
        $this->session->set_flashdata('success', 'Pengaturan promo berhasil diperbarui.');

        redirect('pengaturan/promo');
    }

    private function get_promo_config()
    {
        $config = $this->Config_model->get_all();

        foreach ($this->promo_defaults as $key => $value) {
            if (!isset($config[$key]) || trim((string) $config[$key]) === '') {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    private function set_promo_validation_rules()
    {
        $this->form_validation->set_rules('promo_opening_gratis_qty', 'Gratis kg promo opening', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('promo_opening_label', 'Label promo opening', 'required|trim');
        $this->form_validation->set_rules('promo_daily_min_kg', 'Minimal kg promo daily', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('promo_daily_gratis_qty', 'Gratis kg promo daily', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('promo_daily_label', 'Label promo daily', 'required|trim');
        $this->form_validation->set_rules('promo_sepatu_min_pasang', 'Minimal pasang promo sepatu', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('promo_sepatu_gratis_qty', 'Gratis pasang promo sepatu', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('promo_sepatu_label', 'Label promo sepatu', 'required|trim');
    }

    private function normalize_number($value)
    {
        $number = (float) $value;

        if (floor($number) == $number) {
            return (string) (int) $number;
        }

        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    }
}
