<?php
function mh_board($atts){
	global $mh_board,$paged,$mh_query,$post,$mh_board_link,$board_cat;
	global $mh_board_options,$ID;
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
	//require_once(dirname(__FILE__).'/templates/mh_board_'.$type.'.php');
	/* 082 START */
	ob_start();
	$template_names = array("mh_board_{$type}-{$board_cat}.php", "mh_board_{$type}.php");
	mh_board_load_shortcode_temlate($template_names, true, true);
	wp_reset_query();
	$output = ob_get_clean();
	return $output;
	/* 082 END */
}

/* 082 START */

function mh_board_load_shortcode_temlate($template_names, $load=false, $require_once=true) {
	global $mh_board,$paged,$mh_query,$post,$mh_board_link,$board_cat;
	global $mh_board_options,$ID;
	$located = '';

	foreach ( (array) $template_names as $template_name ) {
		if ( !$template_name )
			continue;
		if ( file_exists(STYLESHEETPATH . '/mh-board/' . $template_name)) {
			$located = STYLESHEETPATH . '/mh-board/' . $template_name;
			break;
		} else if ( file_exists(TEMPLATEPATH . '/mh-board/' . $template_name) ) {
			$located = TEMPLATEPATH . '/mh-board/' . $template_name;
			break;
		} elseif ( file_exists(dirname(__FILE__) . '/templates/' . $template_name) ) {
			$located = dirname(__FILE__) . '/templates/' . $template_name;
			break;
		}
	}
	if ( $load && '' != $located ) {
		if ( $require_once )
			require_once( $located );
		else
			require( $located );
	}

	return $located;
}