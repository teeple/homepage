<?php
function mh_board($atts){
	global $mh_board,$paged,$mh_query,$post,$mh_board_link,$board_cat;
	global $mh_board_options;
	$mh_board_options = get_option('mh_board_options');
	$mh_board_link = get_permalink();
	if(isset($_GET['page_id'])){
		$mh_board_link .= '&';
	}else{
		$mh_board_link .= '?';
	}
	extract(shortcode_atts(array(
      'board_cat' => '',
	 ), $atts));
	$category = get_term_by('name',$board_cat,'board_cat');
	if(!$category){
		$category = get_term_by('slug',$board_cat,'board_cat');
	}
	if(is_object($category)){
		$board_cat = $category->slug;
	}

	$ID = empty($_GET['ID']) ? '' : $_GET['ID'];
	$type = empty($_GET['type']) ? 'list' : $_GET['type'];
	if(!$paged){
		$paged = empty($_GET['page']) ? '1' : $_GET['page'];
	}
	$board_cat = empty($_GET['board_cat']) ? $board_cat : $_GET['board_cat'];
	require_once(dirname(__FILE__).'/templates/mh_board_'.$type.'.php');
	wp_reset_query();
}
?>