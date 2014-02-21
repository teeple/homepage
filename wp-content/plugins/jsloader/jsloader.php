<?php
/*
Plugin Name: jsloader
Plugin URI: http://dhrod0325.blog.me
Description: It's js and css load plugin
Author: oternet
Version: 0.1
Author URI: http://dhrod0325.blog.me
*/

/*  Copyright 2009 -2012 oternet : dhrod0325@naver.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

add_action('init', 'jsLoader');

function jsLoader(){
	add_action('wp_enqueue_scripts', getJs(plugin_dir_path(__FILE__)."js/"));
	add_action('wp_enqueue_scripts', getCss(plugin_dir_path(__FILE__)."css/"));
}

function getJs($path) {
	$script_path = '/'.PLUGINDIR .'/'.$plugin_dir.'jsloader/js/';
	
	$jsList = getFileList($path);
	if(count($jsList)>0){
		foreach($jsList as $js=>$value){
			loadJs('myjs'+$js,$script_path.$value);
		}
	}
}

function getCss($path){
	$style_path = '/'.PLUGINDIR .'/'.$plugin_dir.'jsloader/css/';
	
	$cssList = getFileList($path);
	if(count($cssList)>0){
		foreach($cssList as $key=>$value){
			loadCss('mycss'+$key, $style_path.$value);
		}
	}
}

function loadJs($name,$url){
	wp_deregister_script( $name );
	wp_register_script( $name, $url);
	wp_enqueue_script( $name );
}

function loadCss($name,$url){
	wp_deregister_style($name);
	wp_register_style($name, $url);
	wp_enqueue_style($name);
}


function getFileList($path){
	$list = opendir($path);
	$result = array();
	while ($file = readdir($list)) {
		if($file != "." && $file != "..")
		array_push($result, $file);
	}
	closedir($list);
	return $result;
}

?>