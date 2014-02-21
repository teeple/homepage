<?php
#-----------------------------------------------------------------
# create admin portfolio section
#----------------------------------------------------------------- 
if ( ! function_exists( 'portfolio_register' ) ) {

	function portfolio_register() {  
		
		 global $theme_path;
		 
		 $args = array(
				'hierarchical' => true,
				'label' => __('Portfolio', UT_THEME_NAME),
				'singular_label' => __('Project', UT_THEME_NAME),
				'public' => true,
				'show_ui' => true,
				'capability_type' => 'post',
				'menu_position' => 9,
				'rewrite' => true,
				'menu_icon' => FRAMEWORK_DIRECTORY . 'assets/images/icons/portfolio.png',
				'supports' => array('title', 'editor', 'thumbnail')
		);  
	  
		register_post_type( UT_PORTFOLIO_SLUG , $args );  
	}  
	add_action('init', 'portfolio_register');

}
#-----------------------------------------------------------------
# an new taxonomy for displaying portfolio categories
#----------------------------------------------------------------- 
register_taxonomy("project-type", 
		array(UT_PORTFOLIO_SLUG), 
		array("hierarchical" => true, 
				"label" => __( 'Project Categories', UT_THEME_NAME), 
				"singular_label" => __( 'Project Category', UT_THEME_NAME), 
				"rewrite" => true)
);


#-----------------------------------------------------------------
# Portfolio Admin Filter
#-----------------------------------------------------------------
if ( !function_exists( 'restrict_portfolio_by_category' ) ) {
	function restrict_portfolio_by_category() {
		global $typenow;
		$post_type = UT_PORTFOLIO_SLUG;
		$taxonomy = 'project-type'; 
		if ($typenow == $post_type) {
			$selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
			$info_taxonomy = get_taxonomy($taxonomy);
			wp_dropdown_categories(array(
				'show_option_all' => __("Show All {$info_taxonomy->label}"),
				'taxonomy' => $taxonomy,
				'name' => $taxonomy,
				'orderby' => 'name',
				'selected' => $selected,
				'show_count' => true,
				'hide_empty' => true,
			));
		};
	}
	
	add_action('restrict_manage_posts', 'restrict_portfolio_by_category');
}

if ( !function_exists( 'convert_id_to_term_in_query' ) ) {
	function convert_id_to_term_in_query($query) {
		global $pagenow;
		$post_type = UT_PORTFOLIO_SLUG;
		$taxonomy = 'project-type';
		$q_vars = &$query->query_vars;
		if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
			$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
			$q_vars[$taxonomy] = $term->slug;
		}
	}
}
add_filter('parse_query', 'convert_id_to_term_in_query'); ?>