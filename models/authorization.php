<?php

/**
 * User authorization
 */
class Authorization extends CI_Model {

	function __construct()
	{
		parent::__construct();		
	}
	
	
	/**
	 * Checks username and password in the databas
	 */
	public function checkUser($username, $password)
	{
		$query = "SELECT fu.id FROM fh_users fu
					WHERE fu.username = " . $this->db->escape($username) . "
						AND fu.password = " . $this->db->escape($password) . "";
		$res = $this->db->query($query);
		
		if ($res->num_rows() > 0){
			$row = $res->row();
			if ($row->id > 0){
				return $row->id;
			}
		}
		
		//If username or password is incorrect, destroy session and delete cookies
		$this->session->sess_destroy();	
		$this->input->set_cookie('username64', $username, time() - 60 * 60);
		$this->input->set_cookie('password64', $username, time() - 60 * 60);
			
		return false;
	}
	
	
	/**
	 * Checks username and password, creates session, cookies (if needed)
	 * Returns true if username and password is correct, otherwise returns false
	 */
	public function loginUser($username, $password, $remember)
	{
		$this->load->helper('admin_helper');
		$epass = epass($password);
		if ($user_id = $this->checkUser($username, $epass)){
			$this->session->set_userdata("userid54", $user_id);
			$this->session->set_userdata("username54", $username);
			$this->session->set_userdata("password54", $epass);
			
			//Save username and encrypted password in cookies, if user checks remeber checkbox
			if ($remember){
				$this->input->set_cookie('username64', $username, time() + 60 * 60 * 24 * 3);
				$this->input->set_cookie('password64', $password, time() + 60 * 60 * 24 * 3);
			}
			
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Log out user
	 */
	public function logoutUser()
	{
		$this->session->sess_destroy();
		$this->input->set_cookie('username64', '', time() - 60 * 60);
		$this->input->set_cookie('password64', '', time() - 60 * 60);
	}
	
	
	/**
	 * Checks username and password from session before every request
	 * If session does not exists, checks cookies
	 * Returns true if user is logged in, otherwise returns false
	 */
	public function authorUser()
	{
		$username = $this->session->userdata('username54');
		$password = $this->session->userdata('password54');
		
		if (!$username){
			$from_cookies = true;
			$username = $this->input->cookie('username64');
			$password = $this->input->cookie('password64');
		}
		else {
			$from_cookies = false;
		}
		
		if ($username && $password){
			$user_id = $this->checkUser($username, $password);
			if ($user_id){
				if ($from_cookies){
					$this->session->set_userdata("userid54", $user_id);
					$this->session->set_userdata("username54", $username);
					$this->session->set_userdata("password54", $epass);
				}
				
				if ($this->input->cookie('username64')){
					$this->input->set_cookie('username64', $username, time() + 60 * 60 * 24 * 3);
					$this->input->set_cookie('password64', $password, time() + 60 * 60 * 24 * 3);
				}
				
				return true;
			}
		}
		
		return false;
	}

}