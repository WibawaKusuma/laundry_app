<?php defined('BASEPATH') or exit('No direct script access allowed');

// 1. INI CLASS UTAMA (INDUK)
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
    }

    // Pastikan Anda punya fungsi pengecekan ini dari AI
    public function isAdmin()
    {
        return $this->session->userdata('role') === 'admin';
    }

    public function isStaff()
    {
        return $this->session->userdata('role') === 'staff';
    }
} // <--- PERHATIKAN: Ini adalah tutup kurung class MY_Controller

// ==========================================
// 2. PASTE KODE BARU ANDA DI BAWAH SINI (DI LUAR KURUNG DI ATAS)
// ==========================================

class Admin_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Cek pakai fungsi yang ada di induk (MY_Controller)
        if (!$this->isAdmin()) {
            redirect('auth/login');
        }
    }
}

class Staff_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->isStaff()) {
            redirect('auth/login');
        }
    }
}
