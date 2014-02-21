<?php function DYN_breadcrumbs() {

// Slightly modified script of dimox_breadcrumbs 

  $name = __('Home', 'NorthVantage' ); //text for the 'Home' link
  $before = '<li><span class="subbreak">/</span>';  
  $current_before = '<li class="current_page_item"><span class="subbreak">/</span><span class="text">';
  $current_after = '</span></li>';
  $after = '</li>';
 
  if ( !is_home() && !is_front_page() || is_paged() ) {
  
    global $post;
    $home = home_url();
    echo '<li class="home"><a href="' . $home . '">' . $name . '</a></li>';
 
    if ( is_category() ) {
      global $wp_query;
      $cat_obj = $wp_query->get_queried_object();
      $thisCat = $cat_obj->term_id;
      $thisCat = get_category($thisCat);
      $parentCat = get_category($thisCat->parent);
      if ($thisCat->parent != 0) echo( $before. get_category_parents($parentCat, TRUE, ' / ') . $after);
      
	  if($thisCat->parent != 0) {
			echo '<li class="current_page_item"><span class="text">' . __('Archive by category ', 'NorthVantage' ) .'&#39;';
	  } else {
			echo '<li class="current_page_item"><span class="subbreak">/</span><span class="text">' . __('Archive by category ', 'NorthVantage' ) .'&#39;';
			
	  }
	  
      single_cat_title();
      echo '&#39;' . $current_after;
 
    } elseif ( is_day() ) {
      echo $before . '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $after;
      echo $before . '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $after;
      echo $current_before . get_the_time('d') . $current_after;
 
    } elseif ( is_month() ) {
      echo $before . '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $after;
      echo $before . get_the_time('F') . $after;
 
    } elseif ( is_year() ) {
      echo $current_before . get_the_time('Y') . $current_after;
 
    } elseif ( is_single() ) {
      $cat = get_the_category(); if(isset($cat[0])) $cat = $cat[0];
	  $cat_parent = get_category_parents($cat, TRUE, ' </li><li>');
	if( !is_object( $cat_parent ) ) {
	  $cat_parent = substr($cat_parent, 0, -9); 
  	
      echo $before . $cat_parent;
      echo $current_before;
      the_title();
      echo $current_after;
 	}
	
    } elseif ( is_page() && !$post->post_parent ) {
      echo $current_before;
      the_title();
      echo $current_after;
 
    } elseif ( is_page() && $post->post_parent ) {
      $parent_id  = $post->post_parent;
      $breadcrumbs = array();
      while ($parent_id) {
        $page = get_page($parent_id);
        $breadcrumbs[] = $before . '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>' . $after;
        $parent_id  = $page->post_parent;
      }
      $breadcrumbs = array_reverse($breadcrumbs);
      foreach ($breadcrumbs as $crumb) echo $crumb;
      echo $current_before;
      the_title();
      echo $current_after;
 
    } elseif ( is_search() ) {
      echo $current_before . __('Search results for ', 'NorthVantage' ).'&#39;' . get_search_query() . '&#39;' . $current_after;
 
    } elseif ( is_tag() ) {
      echo $current_before . __('Posts Tagged ', 'NorthVantage' ).'&#39;';
      single_tag_title();
      echo '&#39;' . $current_after;
 
    } elseif ( is_author() ) {
       global $author;
      $userdata = get_userdata($author);
      echo $current_before . __('Articles posted by ', 'NorthVantage' ) . $userdata->display_name . $current_after;
 
    } elseif ( is_404() ) {
      echo $current_before . __('Error ', 'NorthVantage' ) . '404' . $current_after;
    }
 
    if ( get_query_var('paged') ) {
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() );
      echo $current_before . __('Page','NorthVantage') . ' ' . get_query_var('paged') . $current_after;
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() );
    }
 
 
  }
} ?>