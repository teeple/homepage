<?php

/**
 * Templatepart for Slideroutput
 *
 * lambda framework v 2.1
 * by www.unitedthemes.com
 * since lambda framework v 2.0
 */

/* 082 START */
echo '<div id="sub-tabs">';
if(class_exists('bbPress') && is_bbpress()) {
	bbp_breadcrumb();
} elseif ( function_exists('DYN_breadcrumbs') ) {
	echo '<ul>'; DYN_breadcrumbs(); echo '</ul>';
}
echo '</div>';
/* 082 END */

global $slider_meta_data, $lambda_meta_data, $wpdb;

if(is_home()) {
			
	$homeid = get_option('page_for_posts');  
	$slides = get_post_meta($homeid, $slider_meta_data->get_the_id(), TRUE);	
	
		
} elseif(is_shop()) {
	
	$shopid = get_option('woocommerce_shop_page_id');   
	$slides = get_post_meta($shopid, $slider_meta_data->get_the_id(), TRUE);

} else {

	$slides = $slider_meta_data->the_meta();

}	
	
	if(isset($slides['sliderstyle_type']) && ( !( is_archive() || is_search() ) || is_shop() ) ) {
		
		if($slides['sliderstyle_type'] == 'static_image') {
			
			echo '<div id="lambda-featured-header-wrap"><div id="lambda-featured-header">
					<figure class="lambda-featured-header-image">';
			
			//optional url	
			$url = (isset($slides['static_image_url'])) ? $slides['static_image_url'] : '#';	

			echo (isset($slides['static_image'])) ? '<a href="'.$url.'"><img src="'.$slides['static_image'].'" /></a>': '';	
					
			//optional Caption		
			echo (isset($slides['static_image_caption'])) ? '<figcaption class="lambda-featured-header-caption"><span>'.$slides['static_image_caption'].'</span></figcaption>' : '';
					
			echo '</figure></div></div>';		
			
		}
		
		
		if($slides['sliderstyle_type'] == 'static_video') {
			echo '<div id="lambda-featured-header-wrap"><div class="container clearfix"><div class="sixteen columns clearfix">';	
				post_format_video($slides, "fh1");
			echo '</div></div></div>';
				
		}
		
		if($slides['sliderstyle_type'] == 'static_textvideo') {
			echo '<div id="lambda-featured-header-wrap">
					<div class="container clearfix">
						<div class="sixteen columns clearfix" style="padding:20px 0;">';	
						
						echo '<div class="lambda-featured-header-content one_half">
								<h1 style="color:'.$slides['featured_headline_color'].';">'.$slides['featured_headline'].'</h1>
								<p style="color:'.$slides['featured_text_color'].';">'.do_shortcode($slides['featured_text']).'</p>';
								
								if($slides['featured_buttontext'])
								echo '<a class="theme-button medium excerpt" href="'.$slides['featured_link'].'">'.$slides['featured_buttontext'].'</a>';
											
						
						echo '</div><div class="lambda-featured-header-video one_half last"><div class="video-frame">';	
						post_format_video($slides, "fh1");
						echo '</div></div>
					</div>
					</div>
			</div>';
				
		}
		
		
		if($slides['sliderstyle_type'] == 'static_slider') {
			
			$sliderinfo = explode('_',$slides['main_slider']);
			
			//add exception for supersized this one needs to be called in another place
			$table_lambda_sliders = $wpdb->base_prefix . "lambda_sliders"; 
			$supersized = $wpdb->get_var("SELECT slidertype FROM $table_lambda_sliders WHERE id = $sliderinfo[1]");	
			
			if($supersized != 'supersized' || $sliderinfo[0] == 'revslider')	
			lambda_main_slider($slides); // this function can be found in theme-functions.php around line 198
			
		}

	}
	
?>