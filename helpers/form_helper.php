<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('get_countries_form'))
{
    function get_countries_form($db, $language, $placeholder, $country_id = 0)
    {
        $query = "SELECT t.id, t.name FROM fh_countries t WHERE t.language = " . $db->escape($language) . " ORDER BY t.id ASC";
		$res = $db->query($query);
		
		$form = '<select name="country_id" data-placeholder="' . $placeholder . '" class="select-full" tabindex="2">
					<option value=""></option> 
					';
		
		foreach($res->result() as $i=>$e){
			$sel = $e->id == $country_id ? ' selected="selected"' : '';
			$form = $form . '<option value="' . $e->id . '"' . $sel . '>' . $e->name . '</option> 
					';
		}
		
		$form = $form . '</select>';
		
		return $form;
    }   
}


if ( ! function_exists('get_user_groups_form'))
{
    function get_user_groups_form($db, $placeholder, $user_group_id = 0)
    {
        $query = "SELECT t.id, t.name FROM fh_user_groups t ORDER BY t.name ASC";
		$res = $db->query($query);
		
		$form = '<select name="user_group_id" data-placeholder="' . $placeholder . '" class="select-full" tabindex="2">
					<option value=""></option> 
					';
		
		foreach($res->result() as $i=>$e){
			$sel = $e->id == $user_group_id ? ' selected="selected"' : '';
			$form = $form . '<option value="' . $e->id . '"' . $sel . '>' . $e->name . '</option> 
					';
		}
		
		$form = $form . '</select>';
		
		return $form;
    }   
}


if ( ! function_exists('get_user_permissions_form'))
{
	function get_user_permissions_form($user_permissions,  $level = 0)
	{
		$padding = 26;
		
		if ($level > 0){
			echo '<div style="padding-left:' . ($padding * $level) . 'px">
				';
		}
		
		foreach ($user_permissions as $i=>$e){
			$checked = $e['perm_to_group_id'] ? ' checked="checked"' : '';
			
			echo '<div style="padding-bottom:7px;"><input type="checkbox" name="perm' . $i . '" value="1" class="styled"' . $checked . ' /> ' . $e['name'] . '</div>
				';
			
			if ($e['sub']){
				get_user_permissions_form($e['sub'],  $level + 1);
			}
		}
		
		if ($level > 0){
			echo '</div>';
		}
	}
}