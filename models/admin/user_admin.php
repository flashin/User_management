<?php

/**
 * User admin / Also user's own profile management
 */
class User_admin extends CI_Model {
	
	//User id
	private $id;
	
	//Stores user details in an array
	private $params;

	function __construct()
	{
		parent::__construct();
		
		$id = 0;
		
		//Insert empty array in user's details array
		$fields = $this->db->list_fields("fh_users");
		foreach ($fields as $e){
			$this->params[$e] = '';
		}
		
		$this->load->helper('admin_helper');
	}
	
	
	/**
	 * Fills details property with current user data from database
	 */
	public function fillData($id)
	{
		if (!is_numeric($id)){
			return;
		}
		
		$this->id = $id;
		$query = "SELECT u.* FROM fh_users u WHERE u.id = " . $this->db->escape($this->id);
		$res = $this->db->query($query);
		
		if ($res->num_rows() > 0){
			$row = (array)$res->row();
			foreach ($this->params as $i=>$e){
				$this->params[$i] = $row[$i];
			}
		}
	}
	
	
	/**
	 * Update details property from post
	 */
	public function updateFromPost()
	{	
		foreach ($this->params as $i=>$e){
			if ($i == 'image'){
				//If new image is uploaded, save it, delete old image if exists
				if (isset($_FILES[$i]) && is_uploaded_file($_FILES[$i]['tmp_name'])){
					$ext = array_pop(explode('.', basename($_FILES[$i]['name'])));
					$new_name = 'media/imgs/users/user_avatar.' . $ext;
					$this->params[$i] = add_image_to_folder($i, $new_name, $this->params[$i]);
				}
			}
			else if (isset($_REQUEST[$i])){
				//Encrypt password
				if ($i == 'password'){
					$this->params[$i] = epass($_REQUEST[$i]);
				}
				else {
					$this->params[$i] = htmlspecialchars($_REQUEST[$i]);
				}
			}
		}
	}
	
	
	/**
	 * Validate information before record insert / update
	 */
	private function checkBeforeUpload()
	{
		
		// Check image type
		if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])){
			$available = array('image/jpeg', 'image/png', 'image/gif');
			if (!in_array($_FILES['image']['type'], $available)){
				return -1;
			}
			
			if ($_FILES['image']['size'] > 1024 * 1024){
				return -2;
			}
		}
		
		//Check username
		if (isset($_REQUEST['username'])){
			$username = $_REQUEST['username'];
			//Symbol quantity
			if (strlen($username) < 5 || strlen($username) > 20){
				return -3;
			}
			
			//Only letters and digits
			if (!preg_match('/^(([A-z]|[0-9]|(_))+)$/i', $username)){
				return -4;
			}
			
			//If already exists
			if ($username != $this->params['username']){
				$query = "SELECT u.id FROM fh_users u
							WHERE u.username = " . $this->db->escape($username) . " AND u.id != " . $this->id;
				$res = $this->db->query($query);
		
				if ($res->num_rows() > 0){
					return -5;
				}
			}
		}
		
		//Check password
		if (isset($_REQUEST['password'])){
			$password = $_REQUEST['password'];
			//check old password
			if ($this->params['password']){
				if (!isset($_REQUEST['current_password'])){
					return -6;
				}
				if (epass($_REQUEST['current_password']) != $this->params['password']){
					return -6;
				}
			}
			
			//symbol quantity
			if (strlen($password) < 5 || strlen($password) > 20){
				return -7;
			}
			
			//Only letters and digits
			if (!preg_match('/^(([A-z]|[0-9]|(_))+)$/i', $password)){
				return -8;
			}
			
			//repeat password
			if (!isset($_REQUEST['repeat_password'])){
				return -9;
			}
			if ($_REQUEST['repeat_password'] != $password){
				return -9;
			}
		}
		
		return 0;
	}
	
	
	//Creates new user
	public function insertUser()
	{
		$res = $this->checkBeforeUpload();
		
		if ($res < 0){
			return $res;
		}
		
		$this->updateFromPost();
		$user_id = $this->session->userdata('userid54');
		
		$query = "INSERT INTO fh_users (username, password, user_group_id, creator_id, create_date, modifier_id, modify_date)
					VALUES (" . $this->db->escape($this->params['username']) . ",
							" . $this->db->escape($this->params['password']) . ",
							" . $this->db->escape($this->params['user_group_id']) . ",
							" . $this->db->escape($user_id) . ",
							NOW(),
							" . $this->db->escape($user_id) . ",
							NOW()
							)";
		$res = $this->db->query($query);
		
		$this->id = $this->db->insert_id();
		return $this->id;
	}
	
	
	//Updates existing user
	public function updateUser()
	{
		$res = $this->checkBeforeUpload();
		
		if ($res < 0){
			return $res;
		}
		
		$oldparams = $this->params;
		$this->updateFromPost();
		$user_id = $this->session->userdata('userid54');
		
		$exclude_array = array("id", "creator_id", "create_date", "modifier_id", "modify_date");
		
		$update_fields = array();
		foreach ($this->params as $i=>$e){
			if (in_array($i, $exclude_array)){
				continue;
			}
			
			if ($e != $oldparams[$i]){
				$update_fields[] = $i . " = " . $this->db->escape($e);
			}
		}
		
		if ($update_fields){
			$query = "UPDATE fh_users SET " . join(", ", $update_fields) . ",
										modifier_id = " . $this->db->escape($user_id) . ",
										modify_date = NOW()
						WHERE id = " . $this->db->escape($this->id);
			$res = $this->db->query($query);
		}
		
		return 0;
	}
	
	
	//Returns user's details array
	public function getParams()
	{
		return $this->params;
	}
	
}