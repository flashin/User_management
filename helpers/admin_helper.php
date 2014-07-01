<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * encrypts password
 */
if ( ! function_exists('epass'))
{
    function epass($pass = '')
    {
        return md5("o" . md5($pass) . "s");
    }   
}


if ( ! function_exists('delete_image_from_folder'))
{
	function delete_image_from_folder($name)
	{
	if ($name && is_file($name)){
		unlink($name);
		return 1;
	}
	
	return 0;
	}
}


/**
 * saves uploaded image and deletes old if exists
 * Returns image file name
 */
if ( ! function_exists('add_image_to_folder'))
{
    function add_image_to_folder($name, $new_name, $old_image = '')
	{
	if (is_uploaded_file($_FILES[$name]['tmp_name'])){
		if ($old_image){
			delete_image_from_folder($old_image);
		}
	
		move_uploaded_file($_FILES[$name]['tmp_name'], $new_name);
		return $new_name;
	}

	return $old_image;
	} 
}


/**
 * Returns user details
 */
if ( ! function_exists('get_user_details'))
{
	
	function get_user_details($db, $user_id)
	{
		$user_details = array("username" => "",
								"firstname" => "",
								"lastname" => "",
								"email" => "",
								"tel" => "",
								"country_id" => "",
								"country_name" => "",
								"city" => "",
								"image" => "",
								"user_group_id" => "",
								"user_group_name" => "");
		
		$query = "SELECT fu.*, fug.name user_group_name, fc.name country_name
						FROM fh_users fu
							LEFT JOIN fh_user_groups fug ON fug.id = fu.user_group_id 
							LEFT JOIN fh_countries fc ON fc.id = fu.country_id
					WHERE fu.id = " . $db->escape($user_id);
		$res = $db->query($query);
		
		if ($res->num_rows() > 0){
			$row = (array)$res->row();
			foreach ($user_details as $i=>$e){
				if (isset($row[$i])){
					$user_details[$i] = $row[$i];
				}
			}
		}
		
		return $user_details;
	}
}


/**
 * returns user permissions array
 * function is recursive, and returns permissions tree
 */
if ( ! function_exists('get_user_permissions'))
{
	
	function get_user_permissions($db, $user_id)
	{

		$query = "SELECT ptg.user_permission_id
						FROM fh_users u, fh_user_perm_to_group ptg
					WHERE ptg.user_group_id = u.user_group_id AND u.id = " . $db->escape($user_id) . "
					ORDER BY ptg.user_permission_id ASC";
		$res = $db->query($query);
		
		$arr = array();
		if ($res->num_rows() > 0){
			foreach ($res->result() as $i=>$e){
				$arr[] = $e->user_permission_id;
			}
		}
		
		return $arr;
	}
}


/**
 * Returns available languages
 */
if ( ! function_exists('get_languages'))
{
	
	function get_languages($db)
	{

		$query = "SELECT short_name, name FROM fh_languages WHERE visibility = 1
					ORDER BY position ASC";
		$res = $db->query($query);
		
		$arr = array();
		if ($res->num_rows() > 0){
			foreach ($res->result() as $i=>$e){
				$arr[$e->short_name] = $e->name;
			}
		}
		
		return $arr;
	}
}
