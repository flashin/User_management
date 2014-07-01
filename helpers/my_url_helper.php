<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * changes language in the current uri
 */
if ( ! function_exists('get_lang_change_url'))
{

	function get_lang_change_url($languages, $language)
	{
		$url = $_SERVER['REQUEST_URI'];
		
		foreach ($languages as $i=>$e){
			if (preg_match('/\/' . $i . '\//i', $url)){
				return str_replace('/'.$i.'/', '/' . $language . '/', $url);
			}
			else if (preg_match('/(\/' . $i . ')$/i', $url)){
				return str_replace('/'.$i, '/' . $language, $url);
			}
		}
		
		return base_url() . $language . '/' . uri_string();
	}
}
