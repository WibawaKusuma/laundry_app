<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_auth');

        // Load config perusahaan untuk halaman login
        $this->load->model('Config_model');
        $this->load->vars(['company' => $this->Config_model->get_all()]);
    }

    public function index()
    {
        // Fungsi index otomatis dipanggil saat buka controller Auth
        // Kita arahkan langsung ke fungsi login
        $this->login();
    }

    public function login()
    {
        if ($this->session->userdata('status') == 'login') {
            redirect('dashboard');
        }

        if ($this->input->post('username')) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            $check = $this->M_auth->cek_login($username, $password);

            if ($check) {
                $data = array(
                    'user_id' => $check->id,
                    'username' => $check->username,
                    'name' => $check->name,
                    'role' => $check->role,
                    'status' => 'login'
                );
                $this->session->set_userdata($data);
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Username atau password salah');
                redirect('auth/login');
            }
        }

        $this->load->view('auth/login');
    }

    public function logout()
    {
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('username');
        $this->session->unset_userdata('name');
        $this->session->unset_userdata('role');
        $this->session->unset_userdata('status');
        $this->session->set_flashdata('success', 'Anda telah logout');
        redirect('auth/login');
    }
}
