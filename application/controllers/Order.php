<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Order extends MY_Controller
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
        // Route legacy diarahkan ke alur transaksi aktif.
        redirect('transaksi/baru');
    }
}
