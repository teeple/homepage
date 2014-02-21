<?php

if ( !class_exists('CYM_Widget') ):
class CYM_Widget extends WP_Widget {

	/* function __construct( $id_base = false, $name, $widget_options = array(), $control_options = array() ) {
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	} */

	public static function initialize($class_name) {
		register_widget( $class_name );
		return $class_name;
	}

	function cache_get(&$args) {
		$cache = wp_cache_get( 'widget_' . $this->id_base, 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = null;

		if ( isset( $cache[$args['widget_id']] ) ) {
			return $cache[$args['widget_id']];
		}
		return false;
	}

	function cache_set($val, $args) {
		$cache = wp_cache_get( 'widget_' . $this->id_base, 'widget' );
		if ( !is_array( $cache ) )
			$cache = array();
		$cache[$args['widget_id']] = $val;
		wp_cache_set( 'widget_' . $this->id_base, $cache, 'widget' );
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_' . $this->id_base, 'widget' );
	}

}

class CYMW_Bookmarks extends CYM_Widget {

	public static function initialize($class_name) {
		unregister_widget( 'WP_Widget_Links' );
		return parent::initialize($class_name);
	}

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_bookmarks %widgetclass%', 'description' => __( "Customizable blogroll list", CYM_DOMAIN ) );
		parent::__construct('bookmarks', __('Bookmarks', CYM_DOMAIN), $widget_ops);
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);

		$show_notes = (bool)$instance['show_notes'];
		$o = !empty($instance['orderby']) ? $instance['orderby'] : 'name';
		$order = !empty($instance['order']) ? $instance['order'] : 'ASC';
		$cat = !empty($instance['category']) ? (int)$instance['category'] : '';
		$class = !empty($instance['class']) ? ' ' . preg_replace('|\s+|', ' ', trim($instance['class'])) : '';
		$show_images = (int)$instance['show_images'];
		$echo = !isset($instance['echo']) ? true : (bool)$instance['echo'];

		$title = apply_filters('widget_title', !isset($instance['title']) ? __('Bookmarks', CYM_DOMAIN) : $instance['title'], $instance, $this->id_base);

		$before_widget = preg_replace('/id="[^"]*"/', 'id="%id-'.$this->number.'"', $before_widget);
		$before_widget = str_replace(' %widgetclass%', $class, $before_widget);

		$bookmark_args = apply_filters('widget_links_args', array(
			'orderby' => $o, 'order' => $order, 'category' => $cat, 'categorize' => 0,
			'title_before' => $before_title, 'title_after' => $after_title,
			'category_before' => $before_widget, 'category_after' => $after_widget, 
			'show_images' => $show_images, 'show_notes' => $show_notes, 'show_rating' => $show_notes,
			));

		$bookmark_args['echo'] = 0;
		$bookmark_args['title_li'] = $title;

		$bookmark_args = wp_parse_args($bookmark_args, $instance);

		if ($show_notes)
			add_filter('link_rating', array(&$this, 'append_notes'), 99, 2);
		if ($show_images)
			add_filter('get_bookmarks', array(&$this, 'url_filter'), 99, 2);

		$output = wp_list_bookmarks($bookmark_args);

//		if ($output && $show_notes)
//			$output = $this->append_notes($output, $bookmark_args);

		if ($show_notes)
			remove_filter('link_rating', array(&$this, 'append_notes'), 99, 2);
		if ($show_images)
			remove_filter('get_bookmarks', array(&$this, 'url_filter'), 99, 2);
		if ( $echo )
			echo $output;
		return $output;
	}

	function url_filter($res, $args) {
		if (!$args['show_images'] && !$args['show_notes'])
			return $res;
//		if ($args['show_notes'])
		for ($i=0; $i < count($res); $i++) {
			if ($args['show_images'] && '' != $res[$i]->link_image)
				$res[$i]->link_image = bc_default_url_replace(trim($res[$i]->link_image));
		}
		return $res;
	}

	function append_notes($rating, $id) {
		$note = get_bookmark_field('link_notes', $id, 'raw');
		if ( empty($note) )
			return $note;
		if (preg_match('#\[(iframe|script)#i', $note)) {
			$rating = preg_replace('#\[(iframe|script)([^\]]+)\]\s*\[/\1\]#i', '<\1\2></\1>', $note);
			return $rating;
		}
		$rating = '<ul class="bookmark-notes"><li>'.join('</li><li>', split("\n", $note)).'</li></ul>';
		return $rating;
	}

	function _append_notes($html, $args) {
		$bookmarks = get_bookmarks($args);
		if (!$bookmarks)
			return $html;

		$line = split('</li>', $html);
		for ($i=0; $i<count($line); $i++) {
			$out .= $line[$i];
			if ( $notes = trim($bookmarks[$i]->link_notes) )
				$out .= '<ul class="bookmark-notes"><li>'.join('</li><li>', split("\n", $notes)).'</li></ul>';
			$out .= $bookmarks[$i] ? '</li>' : '';
		}
		return $out;
	}

	function _url_filter($url) {
		$url = trim($url);
		if ( '' == $url || strpos($url, '%') === false )
			return $url;
		return bc_default_url_replace($url);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = trim(strip_tags(stripslashes($new_instance['title'])));
		$instance['category'] = $new_instance['category'];
		$instance['show_notes'] = $new_instance['show_notes'];
		$instance['orderby'] = $new_instance['orderby'];
		$instance['order'] = $new_instance['order'];
		$instance['class'] = trim($new_instance['class']);
		$instance['show_images'] = isset($new_instance['show_images']);

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array)$instance, array( 'title' => '', 'show_notes' => false, 'class' => '', 'show_images' => false, 'orderby'=>'name', 'order'=>'ASC', 'empty'=>true, 'category'=> get_option('default_link_category')) );

		$title = esc_attr( $instance['title'] );
		$show_notes = (bool) $instance['show_notes'];
		$category = $instance['category'];
		$orderby = $instance['orderby'];
		$order = $instance['order'];
		$class = $instance['class'];
		$show_images = (bool)$instance['show_images'];

?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php _e( 'Title:', CYM_DOMAIN) ; ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('show_images'); ?>">
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_images'); ?>" name="<?php echo $this->get_field_name('show_images'); ?>"<?php checked( $show_images, true ); ?> />
					<?php _e( 'Show images if exists', CYM_DOMAIN ); ?>
				</label>
				<br />
				<label for="<?php echo $this->get_field_id('shownotes'); ?>">
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('shownotes'); ?>" name="<?php echo $this->get_field_name('show_notes'); ?>"<?php checked( $show_notes, true ); ?> />
					<?php _e( 'Show link notes as list', CYM_DOMAIN ); ?>
				</label>
			</p>
			<p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e( 'Class name:', CYM_DOMAIN ); ?></label>
			<input class="widefat" id="bookmark-class-<?php echo $number; ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo $class; ?>" />
			</p>
			<p>
				<?php _e( 'Order By:', CYM_DOMAIN ); ?>
				<select name="<?php echo $this->get_field_name('orderby'); ?>">
					<option value="name"<?php selected($orderby, 'name'); ?>><?php _e('name', CYM_DOMAIN); ?></option>
					<option value="rating"<?php selected($orderby, 'rating'); ?>><?php _e('rating', CYM_DOMAIN); ?></option>
					<option value="id"<?php selected($orderby, 'id'); ?>><?php _e('link id', CYM_DOMAIN); ?></option>
					<option value="rand"<?php selected($orderby, 'rand'); ?>><?php _e('random', CYM_DOMAIN); ?></option>
				</select>
			</p>
			<p>
			<label><?php _e( 'Order:', CYM_DOMAIN ); ?>
			<select name="<?php echo $this->get_field_name('order'); ?>">
			<option value="ASC"<?php selected($order, 'ASC'); ?>><?php _e('Ascending', CYM_DOMAIN); ?></option>
			<option value="DESC"<?php selected($order, 'DESC'); ?>><?php _e('Descending', CYM_DOMAIN); ?></option>
			</select>
			</label>
			</p>
			<p>
				<label><?php _e('Category:', CYM_DOMAIN); ?></label>
				<select name="<?php echo $this->get_field_name('category'); ?>">
				<?php
					$cats = get_terms( 'link_category', array('hide_empty' => 0) );
					foreach ((array)$cats as $cat) {
				?>
					<option value="<?php echo $cat->term_id; ?>"<?php selected($cat->term_id, $category); ?>><?php echo $cat->name; ?></option>
				<?php
					}
				?>
				</select>
			</p>
<?php
	}

}

// apply widget bookmarks outside of widget.
function cymw_bookmarks($widget_id='footerlinks', $instance='') {
	if (empty($widget_id))
		$widget_id = 'footerlinks';

	$defaults = array('name'=>'sidebar-1', 'id'=>'sidebar-1',
		'before_widget'=>'<div id="bookmarks-'.$widget_id.'" class="widget_bookmarks %widgetclass%">',
		'after_widget'=>'</div>',
		'before_title'=>'<h4>',
		'after_title'=>'</h4>',
		'widget_id'=>"bookmarks-{$widget_id}",
		'widget_name'=>'Bookmarks',
	);
	$params = array($defaults, array('number'=>$widget_id));
	$params = apply_filters( 'dynamic_sidebar_params', $params );

	$default_options = array('show_notes'=>'0', 'orderby'=>'name', 'category'=>'', 'class'=>'', 'show_images'=>'0', 'title'=>'');
	$instance = wp_parse_args($instance, $default_options);

	return the_widget('CYMW_Bookmarks', $instance, $params[0]);
}


register_widget( 'CYMW_Bookmarks' );
endif;// CYM_Widget