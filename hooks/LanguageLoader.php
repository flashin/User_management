<?php

/**
 * loads current language
 * loads default language, if no language is defined in uri
 * loads language file
 */
class LanguageLoader
{
    function initialize() {
        $ci =& get_instance();
        
        $ci->load->helper('url');
		$uri = explode('/', uri_string());
		
		$language = isset($uri[0]) ? $uri[0] : '';
		
		$ci->load->database();
		$query = "SELECT fl.short_name, fl.name
					FROM fh_languages fl
					WHERE fl.visibility = 1
					ORDER BY fl.position ASC";
		$res = $ci->db->query($query);
		
		$ci->language = '';
		$ci->languages = array();
		foreach($res->result() as $i=>$e){
			if (!$ci->language || $e->short_name == $language){
				$ci->language = $e->short_name;
			}
			$ci->languages[$e->short_name] = $e->name;
		}
		
		$ci->data['language'] = $ci->language;
		$ci->data['languages'] = $ci->languages;
		
		$ci->lang->load('admin', $ci->language);
		$ci->data['texts'] = $ci->lang;
		$ci->data['base_url'] = base_url();
    }
}