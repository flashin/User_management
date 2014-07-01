<?php

/**
 * Users List data for grid
 */
class Users_list extends CI_Model {
	
	private $cnt = 0;
	
	//How many users should be shown per page
	private $cnt_per_page = 30;

	function __construct()
	{
		parent::__construct();
	}
	
	
	//Get Users List data as array
	public function getUsersList($page, $sort, $dir)
	{
		$fields = array_keys($this->get_fields());
		if (!in_array($sort, $fields)){
			$sort = $fields[0];
		}
		
		$orders = array("asc", "desc");
		if (!in_array($dir, $orders)){
			$dir = $orders[0];
		}
		
		if (!is_numeric($page)){
			$page = 0;
		}
		
		$query = "SELECT COUNT(1) cnt FROM fh_users";
		$res = $this->db->query($query);
		if ($res->num_rows() > 0){
			$this->cnt = $res->row()->cnt;
		}
		
		$query = "SELECT u.id, u.username, CONCAT(u.firstname, ' ', u.lastname) name, g.name user_group
					FROM fh_users u
						LEFT JOIN fh_user_groups g ON g.id = u.user_group_id
					ORDER BY " . $sort . " " . $dir . "
					LIMIT " . ($page * $this->cnt_per_page) . ", " . $this->cnt_per_page;
		$res = $this->db->query($query);
		
		$arr = array();
		foreach($res->result() as $i=>$e){
			$item = array();
			$a = (array)$e;
			foreach ($fields as $j=>$f){
				$item[$f] = $a[$f];
			}
			$arr[] = $item;
		}
		
		return $arr;
	}
	
	
	//Get pages quantity
	public function getPagesCnt(){
		
		return ceil($this->cnt / $this->cnt_per_page);
	}
	
	
	//Get grid fields
	public function get_fields()
	{
		$fields = array("id"=>"users_list_id",
						"name"=>"users_list_name",
						"username"=>"users_list_username",
						"user_group"=>"users_list_user_group");
		
		return $fields;
	}
	
}