<?php
/**
 * Copyright (c) 2012 Cheon, YoungMin (http://082net.com/)
 * All rights reserved.
 *
 **/

require_once( dirname(__FILE__) . '/breadcrumbs.php' );
require_once( dirname(__FILE__) . '/widgets.php' );
//require_once( dirname(__FILE__) . '/pressthis-mod.php' );
/* if ( !class_exists('WP_DMGR') )
	require_once( dirname(__FILE__) . '/download-mgr/download-mgr.php' ); */

function uangel_after_setup_theme() {
	//add_theme_support( 'mhboard' );
}
add_action('after_setup_theme', 'uangel_after_setup_theme');

function uangel_wp_enqueue_scripts() {
	wp_enqueue_style('082-uangel', get_stylesheet_directory_uri() . '/082/assets/css/style.css', false, '20130306');
}
add_action('wp_enqueue_scripts', 'uangel_wp_enqueue_scripts');

function uangel_footer_links() {
?>
<div class="family-sites-footer">
<select onchange="document.location.href=this.options[this.selectedIndex].value;" name="family-sites-dropdown">
	<option value="#" class="family"> FAMILY SITES </option>
<?php
	$bookmarks = array();
	$bookmarks = get_bookmarks("category=42");
	if ($bookmarks[0] != '') {
		foreach ( $bookmarks as $bookmark ) { ?>
	<option value="<?php echo clean_url($bookmark->link_url); ?>"> <?php echo $bookmark->link_name; ?> </option>
<?php }
	}
?>
</select>
</div>
<?php
}


/* Copied from render_lambda_blog() */
if( !function_exists( 'render_lambda_mhboard' ) ) {
	function render_lambda_mhboard($box_type, $metadata) {
	if ( $box_type != 'mhboard' || !function_exists('mh_board') )
		return true;
		
		global $lambda_meta_data, $post, $theme_options;
		
		global $mh_board_link;
		$board_page_id = 3586;
		$mh_board_link = get_page_link($board_page_id);

		$z = 1;		
		$args= array (
			'post_type' => 'board',
			'post_status' => 'publish',
			'posts_per_page'=>5,
			'board_cat' => 'news',
			//'page'=>1,
			'orderby' =>'post_date',
			'order' => 'DESC'
		);
		
		query_posts( $args );
		if (have_posts()) : while (have_posts()) : the_post(); $lambda_meta_data->the_meta();
		
		global $more; 
		$more = ($metadata['activate_blog_excerpt'] == 'on') ? 1 : 0;
		$bloggrid = ( isset($metadata['blog_grid']) ) ? $metadata['blog_grid'] : 'one_third';	
		
		
		$gridcount = array('full-width'	=> '1',
						   'one_third' 	=> '3',
			  			   'one_half'  	=> '2',
			  			   'one_fourth'	=> '4');		
			
		?>
		
		<section class="post <?php echo $bloggrid; ?> <?php if($z%$gridcount[$bloggrid]==0) { echo 'last'; } ?>" id="post-<?php the_ID(); ?>">
		<article class="entry-post clearfix">
		
		<header class="entry-header clearfix">
			
			<?php 
			
			$pformat = get_post_format(); 
			$postformat = (!empty( $pformat )) ? $pformat : 'standard'; 
								
			?>
			  
			<h1 class="entry-title <?php echo $postformat; ?>-post-title">
				<a href="<?php echo mh_board_view_link(get_the_ID()); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', UT_THEME_NAME ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h1>
			
			<div class="entry-meta row clearfix">
				
				<div class="post-ut">
					<?php echo lambda_posted_on_for_mh(); ?>
				</div> <!-- post date -->
								 
		</div><!-- .entry-meta -->
							   
		</header>  
		
		<?php 
		$post_format = get_post_format();	
		$post_format = ( isset($postformat['portfolio_type']) && $postformat['portfolio_type'] == 'image_portfolio') ? 'gallery' : $post_format;		
		
		if($metadata['activate_blog_images'] == 'on')
		get_template_part( 'post-formats/' . $post_format ); 
		
		if(has_post_thumbnail(get_the_ID()) && $metadata['activate_blog_images'] == 'on' && $post_format != 'video') :
			
			$imgID = get_post_thumbnail_id($post->ID);		
			$url = wp_get_attachment_url( $imgID ); 
			$alt = get_post_meta($imgID , '_wp_attachment_image_alt', true);			
			
			echo '<div class="thumb"><div class="post-image"><div class="overflow-hidden imagepost">';
			echo '<img class="wp-post-image" alt="'.trim( strip_tags($alt) ).'" src="'.$url.'" />';
			echo '<a title="'.get_the_title().'" href="'.mh_board_view_link(get_the_ID()).'"><div class="hover-overlay"><span class="circle-hover"><img src="'.get_template_directory_uri().'/images/circle-hover.png" /></span></div></a>';
			echo '</div></div></div>';
								
		endif;
		?>	
		
		
		</article>
		</section>		
		
		<?php if(($z%$gridcount[$bloggrid]==0) && $bloggrid != 'full-width') { ?>
			<div class="clear"></div>
		<?php } ?>
		
		<?php $z++; endwhile; ?>
		<div class="read-more">
		<a href="<?php echo $mh_board_link; ?>"><img width="83" height="17" alt="" src="<?php echo get_stylesheet_directory_uri() ?>/082/assets/img/button_more_11.png" class="size-full wp-image-5972 alignright" style="opacity: 1;"></a>
		</div>
		<?php endif; ?>
		<?php wp_reset_query(); ?>
               
                
	<?php
	}
}
add_action('lambda_box_page_item', 'render_lambda_mhboard', 10, 2);

function lambda_posted_on_for_mh() {
	$mhpost_link = get_mh_board_view_link(get_the_ID());
	$author_link = remove_query_arg( array('ID', 'type'), $mhpost_link );
	$author_link = add_query_arg( array('author' => get_the_author_meta( 'ID' )), $author_link );
	return sprintf( __( '%2$s %3$s', UT_THEME_NAME ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			$mhpost_link,
			esc_attr( get_the_time() ),
			get_the_date()
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			$author_link,
			sprintf( esc_attr__( 'View all posts by %s', UT_THEME_NAME ), get_the_author() ),
			get_the_author()
		)
	);
}

function uangel_wp_head_scripts() {
?>
<script type="text/javascript">
/* <![CDATA[ */
function uangel_popup(u, n, w, h) {
	if ( typeof u == 'object' ) u = u.href;
	n = n || 'uangel_popup'; w = w || 700; h = h || 400;
	if ( !window.open(u,'fbpopup','toolbar=0,resizable=1,scrollbars=1,status=1,width='+w+',height='+h) )
		document.location.href=u;
	return false;
}
/* ]]> */
</script>
<?php
}
add_action('wp_head', 'uangel_wp_head_scripts', 10);

function uangel_wp_head_styles() {
}
add_action('wp_head', 'uangel_wp_head_styles', 9);

function uangel_allow_hwp_upload( $mimes ) {
	$mimes['hwp'] = 'application/octet-stream';
	return $mimes;
}
add_filter('upload_mimes', 'uangel_allow_hwp_upload');

function unangel_force_tinymce_default($r) {
	return 'tinymce';
}
add_filter('wp_default_editor', 'unangel_force_tinymce_default');