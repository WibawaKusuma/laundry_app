<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Perhatikan: extends Staff_Controller
// Artinya: Staff boleh buka, Admin juga boleh (tergantung logika di MY_Controller)
class Order extends Staff_Controller
{

    public function index()
    {
        // Load view order di sini
        $this->load->view('order/index');
    }
}
