<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_auth extends CI_Model
{

	public function cek_login($username, $password)
	{
		$user = $this->db->get_where('m_users', ['username' => $username])->row();

		if (!$user) {
			return false;
		}

		$stored_password = (string) $user->password;
		$is_bcrypt = password_get_info($stored_password)['algo'] !== null;

		if ($is_bcrypt && password_verify($password, $stored_password)) {
			return $user;
		}

		if (!$is_bcrypt && md5($password) === $stored_password) {
			// Upgrade hash lama ke bcrypt saat user berhasil login.
			$new_hash = password_hash($password, PASSWORD_DEFAULT);
			$this->db->where('id', $user->id);
			$this->db->update('m_users', ['password' => $new_hash]);
			$user->password = $new_hash;
			return $user;
		}

		return false;
	}
}
