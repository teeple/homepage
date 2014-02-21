<?php
require('./wp-load.php');

if ( !empty($_GET['fix_domain']) ):

//$blogs = get_blog_list(0, 'all');

$str_from = 'www.uangel.com/en';
$str_to = 'www.uangel.com';

foreach( array('recently_edited', 'dashboard_widget_options', 'ftp_credentials') as $delop )
	delete_option($delop);

foreach ( array('home', 'siteurl') as $opname ) {
	if ( $cur_url = get_option($opname) ) {
		$cur_url = str_replace(rtrim($str_from, '/'), rtrim($str_to, '/'), $cur_url);
		update_option($opname, $cur_url);
	}
}

$query = "UPDATE $wpdb->posts SET post_content=REPLACE(post_content, '$str_from', '$str_to'), guid=REPLACE(guid, '$str_from', '$str_to')";

$query2 = "UPDATE $wpdb->postmeta SET meta_value=REPLACE(meta_value, '$str_from', '$str_to') WHERE meta_key LIKE '_menu_item_url' AND meta_value LIKE '%uangel.com%'";

$wpdb->query($query);
$wpdb->query($query2);

$str_from_ = addslashes($str_from);
$str_to_ = addslashes($str_to);
$query3 = $wpdb->prepare("UPDATE wp_revslider_slides SET params=REPLACE(params, %s, %s) WHERE params LIKE %s", $str_from_, $str_to_, $str_from_);

/* $options = $wpdb->get_results("SELECT option_id, option_name, option_value FROM $wpdb->options WHERE option_value LIKE '%gabia-si.com%'");
if ( $options ) {

} */
echo 'Done';
endif;

if ( !empty($_GET['fix_lamda']) ):
/* SELECT *
FROM `wp_postmeta`
WHERE `meta_value` LIKE '%wktest%'
OR meta_value LIKE '%uangel.com%'
AND meta_key NOT LIKE '_menu_item_url'
*/

$res = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key IN ('nevada_slider', 'nevada_metapanel') AND ( meta_value LIKE '%uangel.com%' OR meta_value LIKE '%wktest.cafe24.com%' )");

$str_from = array('wktest.cafe24.com', 'pms.uangel.com', 'www.uangel.com/en', '//uangel.com/en');
$str_to = array('www.uangel.com', 'www.uangel.com', 'www.uangel.com', '//www.uangel.com');

if ( strpos($_SERVER['REQUEST_URI'], '/ko/') !== false ) {
	$str_from = array('wktest.cafe24.com', 'pms.uangel.com', 'www.uangel.com', '//uangel.com');
	$str_to = array('www.uangel.com/ko', 'www.uangel.com/ko', 'www.uangel.com/ko', '//www.uangel.com/ko');
}
var_dump($str_to);
/* if ( $res ) {
	foreach ( $res as $r ) {
		$_id = $r->post_id;
		$_mkey = $r->meta_key;
		$val = get_post_meta($_id, $_mkey, true);
		//var_dump($val);
		foreach( $val as $k => $v ) {
			if ( $k == 'lambda_page_item' ) {
				foreach ( $v as $i => $j ) {
					$val[$k][$i]['extra_content'] = str_replace($str_from, $str_to, $j['extra_content']);
				}
			} elseif ( is_string($v) && ( strpos($v, 'wktest.cafe24.com') !== false || strpos($v, 'uangel.com') !== false ) ) {
				$val[$k] = str_replace($str_from, $str_to, $v);
			}
		}
		//var_dump($val);
		update_post_meta($_id, $_mkey, $val);
		var_dump($_id, $_mkey);
	}
} */


echo 'DONE!';
endif;