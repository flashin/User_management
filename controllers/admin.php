<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {
	
	private $page_permission = 0;
	
	function __construct()
	{
        parent::__construct();
		
		//Load helpers
        $this->load->helper('admin_helper');
		$this->load->helper('my_url_helper');
    }


	/**
	 * Default page
	 */
	public function index()
	{
		$this->check_user();
		
		$this->load->view('admin/default', $this->data);
	}
	
	
	/**
	 * Login page
	 */
	public function login($error = 0)
	{	
		if ($error){
			//Set error if defined
			$this->data["login_error"] = $this->lang->line("login_win_login_error");
		}
		$this->load->view('admin/login', $this->data);
	}
	
	
	/**
	 * Change profile, create new user
	 * if id > 0, change profile, otherwise add new user
	 * profile_type - if 'general', changes general info, otherwide security info
	 */
	public function profile($id = 0, $profile_type = 'general', $save_result = 0)
	{	
		$this->check_user();
		
		$this->load->model('admin/User_admin');
		if ($id && $id == "add"){
			$id = 0;
		}
		else if (!$id || !is_numeric($id)){
			$id = $this->session->userdata('userid54');	
		}
		
		//Check permission if editing other profile
		if ($id != $this->session->userdata('userid54') && !in_array(2, $this->data['my_groupsperm'])){
			redirect($this->data['language'] . '/admin');
		}
		
		$this->User_admin->fillData($id);
		$this->data['edit_user_id'] = $id;
		$this->data['edit_details'] = get_user_details($this->db, $id);
		
		if ($profile_type == 'general'){
			$this->data['profile_template'] = 'profile_general';
		}
		else {
			$this->data['profile_template'] = 'profile_security';
		}
		
		//Save url
		$action = $id > 0 ? $id : 'add';
		$this->data['submit_url'] = $this->data['base_url'] . $this->data['language'] . '/admin/change_user/' . $action . '/' . $profile_type;
		
		if ($save_result < 0){
			$this->data['submit_result_msg'] = $this->lang->line('user_edit_err_' . (-1 * $save_result));
		}
		else if ($save_result == 1){
			$this->data['submit_result_msg'] = $this->lang->line('user_edit_success');
		}
		
		$this->load->view('admin/profile', $this->data);
	}


	/**
	 * Users list
	 */
	public function users($page = 0, $sort = 'name', $dir = 'asc')
	{
		$this->check_user();
		
		$this->load->model('admin/Users_list');
		$this->data['users_list'] = $this->Users_list->getUsersList($page, $sort, $dir);
		$this->data['pages_cnt'] = $this->Users_list->getPagesCnt();
		$this->data['table_fields'] = $this->Users_list->get_fields();
		$this->data['table_page'] = $page;
		$this->data['table_sort'] = $sort;
		$this->data['table_dir'] = $dir;
		
		$this->load->view('admin/users', $this->data);
	}


	/**
	 * User groups
	 */
	public function user_groups()
	{
		$this->check_user();
		
		$this->load->model('admin/User_group');
		$this->data['user_groups_list'] = $this->User_group->getUserGroupsList();
		$this->data['table_fields'] = $this->User_group->getUserGroupsListFields();
		
		$this->load->view('admin/user_groups', $this->data);
	}
	
	
	/**
	 * Manage user group permissions
	 * if $id = 0, then insert new user group form
	 * save_result, if returning from saving action
	 */
	public function group_permissions($id, $save_result = 0)
	{
		$this->check_user();
		
		if (!$id || !is_numeric($id)){
			$id = 0;
			$action = 'add';
		}
		else {
			$action = $id;
		}
		
		$this->load->model('admin/User_group');
		$this->data['user_group_name'] = $this->User_group->getName($id);
		$this->data['user_group_permissions'] = $this->User_group->getPermissions($id);
		
		if ($save_result == 1){
			$this->data['submit_result_msg'] = $this->lang->line('user_groups_edit_success');
		}
		
		$this->load->view('admin/group_permissions', $this->data);
	}
	
	
	/**
	 * Change user profile action
	 * if $id = 0, then creates new user
	 * $profile_type, what type of information is changing
	 */
	public function change_user($id, $profile_type)
	{
		$this->check_user();
		
		$this->load->model('admin/User_admin');
		if ($id && $id == "add"){
			$id = 0;
		}
		else if (!$id || !is_numeric($id)){
			$id = $this->session->userdata('userid54');	
		}
		
		//Check permission if editing other profile
		if ($id != $this->session->userdata('userid54') && !in_array(2, $this->data['my_groupsperm'])){
			redirect($this->data['language'] . '/admin');
		}
		
		$this->User_admin->fillData($id);
		
		if ($id > 0){
			$res = $this->User_admin->updateUser();
			$redirect_url = $this->data['language'] . '/admin/profile/' . $id . '/' . $profile_type . '/';
			$redirect_url .= $res < 0 ? $res : 1;
		}
		else {
			$res = $this->User_admin->insertUser();
			if ($res < 0){
				$redirect_url = $this->data['language'] . '/admin/profile/add/security/' . $res;
			}
			else {
				$redirect_url = $this->data['language'] . '/admin/profile/' . $res . '/general/1';
			}
		}
		
		redirect($redirect_url);
	}
	
	
	/**
	 * Updates user's group and its permissions
	 * if $id = 0, then creates new group
	 */
	public function change_user_group($id)
	{
		$this->check_user();
		
		if (!$id || !is_numeric($id)){
			$id = 0;
		}
		
		$this->load->model('admin/User_group');
		
		if ($id > 0){
			$this->User_group->updateGroup($id);
		}
		else {
			$id = $this->User_group->insertGroup();
		}
		
		$this->User_group->updateGroupPermissions($id);
		
		$redirect_url = $this->data['language'] . '/admin/group_permissions/' . $id . '/1';
		redirect($redirect_url);
	}
	
	
	/**
	 * Authorizes user
	 */
	public function authorize()
	{
		$success = false;
		if (isset($_REQUEST['username']) && isset($_REQUEST['password'])){
			$username = $_REQUEST['username'];
			$password = $_REQUEST['password'];
			$remember = isset($_REQUEST['remember']) && $_REQUEST['remember'] ? 1 : 0;
			
			$this->load->model('Authorization');
			$success = $this->Authorization->loginUser($username, $password, $remember);
		}
		
		if ($success){
			redirect($this->language . '/admin');
		}
		else {
			redirect($this->language . '/admin/login/error');
		}
	}
	
	
	/**
	 * User logout action
	 */
	public function logout()
	{
		$this->load->model('Authorization');
		$this->Authorization->logoutUser();
		
		redirect($this->language . '/admin/login');
	}
	
	
	/**
	 * Checks user
	 * Is called in every request
	 */
	public function check_user()
	{
		$this->load->model('Authorization');
		
		$eps = $this->Authorization->authorUser();
		if (!$eps){
			redirect($this->language . '/admin/login');
		}
		
		$this->data["my_user_id"] = $this->session->userdata('userid54');
		$this->data["my_details"] = get_user_details($this->db, $this->data["my_user_id"]);
		
		//Loads permissions
		$this->data["my_groupsperm"] = get_user_permissions($this->db, $this->data["my_user_id"]);
	}
}
