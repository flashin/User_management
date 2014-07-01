<?php

/**
 * User groups and group permissions management
 */
class User_group extends CI_Model {

	
	function __construct()
	{
		parent::__construct();
	}
	
	
	//Get all groups list
	public function getUserGroupsList()
	{
	
		$query = "SELECT g.id, g.name FROM fh_user_groups g ORDER BY g.name ASC";
		$res = $this->db->query($query);
		
		$arr = array();
		$fields = $this->getUserGroupsListFields();
		foreach($res->result() as $i=>$e){
			$item = array();
			$a = (array)$e;
			foreach ($fields as $j=>$f){
				$item[$j] = $a[$j];
			}
			$arr[] = $item;
		}
		
		return $arr;
	}
	
	
	//Get fields of groups list
	public function getUserGroupsListFields()
	{
		$fields = array("id"=>"user_groups_list_id",
						"name"=>"user_groups_list_name");
		
		return $fields;
	}
	
	
	//Get group name
	public function getName($id)
	{
		if ($id > 0){
			$query = "SELECT g.name FROM fh_user_groups g WHERE g.id = " . $this->db->escape($id);
			$res = $this->db->query($query);
		
			if ($res->num_rows() > 0){
				return $res->row()->name;
			}
		}
		
		return '';
	}
	
	
	/**
	 * Get permissions for group, recursive function, gets child permissions
	 */
	public function getPermissions($user_group_id, $parent_id = 0)
	{
		$query = "SELECT p.id, p.name, p.parent_id, ptg.id perm_to_group_id
					FROM fh_user_permissions p
						LEFT JOIN fh_user_perm_to_group ptg ON ptg.user_permission_id = p.id AND ptg.user_group_id = " . $this->db->escape($user_group_id) . "
					WHERE p.parent_id = " . $this->db->escape($parent_id);
		$res = $this->db->query($query);
		
		$arr = array();
		if ($res->num_rows() > 0){
			foreach ($res->result() as $i=>$e){
				$arr[$e->id] = array('id'=>$e->id, 'name'=>$e->name, 'parent_id'=>$e->parent_id, 'perm_to_group_id'=>$e->perm_to_group_id);
				$arr[$e->id]['sub'] = $this->getPermissions($user_group_id, $e->id);
			}
		}
		
		return $arr;
	}
	
	
	//inserts new group
	public function insertGroup()
	{
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		
		$query = "INSERT INTO fh_user_groups (name) VALUES (" . $this->db->escape($name) . ")";
		$res = $this->db->query($query);
		
		return $this->db->insert_id();
	}
	
	
	//updates existing group
	public function updateGroup($id)
	{
		$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		$old_name = $this->getName($id);
		
		if ($old_name != $name){
			$query = "UPDATE fh_user_groups g SET g.name = " . $this->db->escape($name) . "
						WHERE g.id = " . $this->db->escape($id);
			$res = $this->db->query($query);
		}
	}
	
	
	//inserts / updates group's permissions
	public function updateGroupPermissions($id, $parent_id = 0)
	{
		$perms = $this->getPermissions($id, $parent_id);
		$user_id = $this->session->userdata('userid54');
		
		foreach ($perms as $i=>$e){
			$is_checked = isset($_REQUEST['perm' . $i]) && $_REQUEST['perm' . $i] ? 1 : 0;
			
			if ($is_checked && !$e['perm_to_group_id']){
				$query = "INSERT INTO fh_user_perm_to_group
							(user_group_id, user_permission_id, creator_id, create_date, modifier_id, modify_date)
							VALUES (" . $this->db->escape($id) . ",
									" . $this->db->escape($i) . ",
									" . $this->db->escape($user_id) .",
									NOW(),
									" . $this->db->escape($user_id) .",
									NOW()
									)";
				$res = $this->db->query($query);
			}
			else if (!$is_checked && $e['perm_to_group_id'] > 0){
				$query = "DELETE FROM fh_user_perm_to_group ptg WHERE ptg.id = " . $this->db->escape($e['perm_to_group_id']);
				$res = $this->db->query($query);
			}
			
			if ($e['sub']){
				$this->updateGroupPermissions($id, $i);
			}
		}
	}
	
}