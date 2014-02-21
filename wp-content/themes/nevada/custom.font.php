<?php
#-----------------------------------------------------------------
# CSS Header Settings - do not remove this part!
#-----------------------------------------------------------------
header("Content-type: text/css; charset: UTF-8");
header("Cache-Control: must-revalidate");

$absolute_path = __FILE__;
$path_to_file = explode( 'wp-content', $absolute_path );
$path_to_wp = $path_to_file[0];

// Access to WordPress
require_once( $path_to_wp . '/wp-load.php' );

$offset = 60 * 60 ;
$ExpStr = "Expires: " .
gmdate("D, d M Y H:i:s",
time() + $offset) . " GMT";
header($ExpStr);


#-----------------------------------------------------------------
# Load Option Tree
#-----------------------------------------------------------------
$custom_font = get_option('option_tree');

$fontface = '';
if(is_array($custom_font['custom_font'])) :

foreach ($custom_font['custom_font'] as $key => $value) {
			
	$fontface .= "@font-face {";
		
		if(isset($value['title']) && !empty($value['title']))
		$fontface .= "font-family: '".$value['title']."';". "\n";
		
		if(isset($value['embedded-opentype']) && !empty($value['embedded-opentype'])) {
			$fontface .= "src: url('".$value['embedded-opentype']."');". "\n";		
			$fontface .= "src: url('".$value['embedded-opentype']."?#iefix') format('embedded-opentype'),". "\n";
		}
		
		if(isset($value['woff']) && !empty($value['woff']))
		$fontface .= "	   url('".$value['woff']."') format('woff'),". "\n";
		
		if(isset($value['truetype']) && !empty($value['truetype']))
		$fontface .= "	   url('".$value['truetype']."') format('truetype'),". "\n";
		
		if(isset($value['svg']) && !empty($value['svg']))
		$fontface .= "	   url('".$value['svg']."') format('svg');". "\n";
		
		
	$fontface .= "}";

}

endif;

echo $fontface;