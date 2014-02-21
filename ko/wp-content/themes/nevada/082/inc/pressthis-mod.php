<?php
/*
Plugin Name: Press This Reloaded Mod
Version: 1.0.3.1
Description: Press This, using the regular Add New Post screen
Author: scribu
Author URI: http://scribu.net
Plugin URI: http://scribu.net/wordpress/press-this-reloaded


Copyright (C) 2010-2011 Cristi Burcă (scribu@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class Press_This_Reloaded_Mod {

	private static $title;
	private static $content;

	function init() {
		//add_filter('shortcut_link', array( __CLASS__, 'shortcut_link'));
		add_filter('redirect_post_location', array(__CLASS__, 'redirect'));

		if ( isset($_GET['u']) || isset($_GET['press-this']) ) {
			add_action('load-post-new.php', array( __CLASS__, 'load'));
			add_action('load-post.php', array( __CLASS__, 'load'));
		}
	}

	function shortcut_link($link) {
		$link = str_replace('press-this.php', 'post-new.php', $link);
		$link = str_replace('width=720', 'width=1080', $link);// 082
		/* 082 START */
		$link = str_replace('height=570', 'height=720', $link);// 082
		$link = str_replace('createRange().Text', 'createRange().htmlText', $link);
		$link = str_replace(',s=(e?e():(k)?k()', ',g=function(e){var%20r=e.getRangeAt(0);var%20c=r.startContainer;var%20n=c.ownerDocument.createElement("layer");var%20df=r.extractContents();n.appendChild(df);r.insertNode(n);return%20n.innerHTML;},s=(e?g(e()):(k)?g(k())', $link);
		/* 082 END */

		return $link;
	}

	function redirect($location) {
		$referrer = wp_get_referer();

		if ( false !== strpos($referrer, '?u=' ) || false !== strpos($referrer, '&u=' ) || false !== strpos($referrer, '?press-this=' ) || false !== strpos($referrer, '&press-this=' ) )
			$location = add_query_arg('press-this', 1, $location);

		return $location;
	}

	function load() {
		$title = isset( $_GET['t'] ) ? trim( strip_tags( html_entity_decode( stripslashes( $_GET['t'] ) , ENT_QUOTES) ) ) : '';

		$url = isset($_GET['u']) ? esc_url($_GET['u']) : '';
		$url = wp_kses(urldecode($url), null);
		if ( preg_match('|https?://([^/]+)|i', $url, $root_uri) ) {
			$root_uri = $root_uri[0];
		}

		$selection = '';
		/* 082 START */
		if ( !empty($_GET['s']) ) {
			$selection = str_replace('&apos;', "'", stripslashes($_GET['s']));
			//$selection = trim( htmlspecialchars( html_entity_decode($selection, ENT_QUOTES) ) );
			$selection = html_entity_decode($selection, ENT_QUOTES);
			$selection = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $selection );
			$selection = strip_tags($selection, '<p><br><img><a>');
			$selection = preg_replace('#<p([^>]+)>#i', '<p>', $selection);
			$selection = preg_replace('#<br\s*>#i', '<br />', $selection);

			if ( isset($root_uri) )
				$selection = preg_replace('#\s+(src|href)=("|\')/#i', ' \1=\2'.$root_uri.'/', $selection);
			$selection = wpautop($selection);
			$selection = trim($selection);
		}

		self::$content = '';
		if ( !empty($selection) ) {
			//self::$content  = "<blockquote>$selection</blockquote>\n\n";
			self::$content  = "$selection\n\n";
			//self::$content .= __('via ') . sprintf( "<a href='%s'>%s</a>.</p>", esc_url( $url ), esc_html( $title ) );
		} else {
			//self::$content = $url;
		}

		if ( !defined('IFRAME_REQUEST') )// disable admin bar and more...
			define('IFRAME_REQUEST', true);

		add_filter('hidden_meta_boxes', array(__CLASS__, 'hidden_meta_boxes'), 10, 3);
		add_action('post_submitbox_misc_actions', array(__CLASS__, 'wp_dashboard_recent_drafts'));
		add_action('wp_print_scripts', array(__CLASS__, 'disable_autosave'));
		/* 082 END */

		self::$title = $title;

		add_action('admin_print_styles', array( __CLASS__, 'style'));

		if ( !empty(self::$content) ) {
			add_filter('default_title', array(__CLASS__, 'default_title'));
			add_filter('default_content', array(__CLASS__, 'default_content'));
		}

		add_filter( 'show_admin_bar', '__return_false' );
	}

	function default_title() {
		return self::$title;
	}

	function default_content() {
		return self::$content;
	}

	function style() {
?>
<style type="text/css">
/* hide the header */
/* #wphead, #screen-meta, #icon-edit, h2 {display: none !important} */

/* hide the menu */
#wpbody {margin-left:7px !important}

/* hide the footer */
#wpfooter, #footer {display: none !important}
#wpcontent {padding-bottom: 0 !important}
#normal-sortables {margin-bottom: -20px !important}

/* 082 */
#adminmenuback, #adminmenuwrap {display: none !important;}
#wpcontent {margin-left: 0 !important;}
#screen-meta, #screen-meta-links, .update-nag {display:none !important;}

#submitdiv .recent_drafts h4 {
margin: 0;
}
#submitdiv .recent_drafts ul, #submitdiv .recent_drafts p {
margin: 0.5em 0;
}
</style>
<?php
	}

	/* 082 START */
	function hidden_meta_boxes($hidden, $screen, $use_defaults) {
		global $wp_meta_boxes;
		if ( $screen->id != 'post' )
			return $hidden;

		$allowed = array('tagsdiv-post_tag', 'categorydiv', 'postimagediv', 'submitdiv', 'revisionsdiv');
		$hidden = array();

		// copied form meta_box_prefs();
		foreach ( array_keys($wp_meta_boxes[$screen->id]) as $context ) {
			foreach ( array_keys($wp_meta_boxes[$screen->id][$context]) as $priority ) {
				foreach ( $wp_meta_boxes[$screen->id][$context][$priority] as $box ) {
					if ( false == $box || ! $box['title'] )
						continue;
					// Submit box cannot be hidden
					if ( 'submitdiv' == $box['id'] || 'linksubmitdiv' == $box['id'] )
						continue;
					if ( !in_array($box['id'], $allowed ) )
						$hidden[] = $box['id'];
				}
			}
		}
		return $hidden;
	}

	function disable_autosave() {
		wp_deregister_script('autosave');
	}

	// copied from wp-admin/includes/dashboard.php
	function wp_dashboard_recent_drafts( $drafts = false ) {
		$post = !empty($_GET['post']) ? (int)$_GET['post'] : 0;// 082
		if ( !$drafts ) {
			$drafts_query = new WP_Query( array(
				'post_type' => 'post',
				'post_status' => 'draft',
				'author' => $GLOBALS['current_user']->ID,
				'posts_per_page' => 5,
				'orderby' => 'modified',
				'order' => 'DESC',
				'post__not_in' => array($post)// 082
			) );
			$drafts =& $drafts_query->posts;
		}
		echo '<div class="misc-pub-section recent_drafts">';
		echo '<h4>' . __('Recent Drafts') . '</h4>';// 082
		if ( $drafts && is_array( $drafts ) ) {
			$list = array();
			foreach ( $drafts as $draft ) {
				$url = get_edit_post_link( $draft->ID );
				$url = add_query_arg(array('u' => 1, 'press-this' => 1), $url);// 082
				$title = _draft_or_post_title( $draft->ID );
				$item = "<h4><a href='$url' title='" . sprintf( __( 'Edit &#8220;%s&#8221;' ), esc_attr( $title ) ) . "'>" . esc_html($title) . "</a> <abbr title='" . get_the_time(__('Y/m/d g:i:s A'), $draft) . "'>" . get_the_time( get_option( 'date_format' ), $draft ) . '</abbr></h4>';
				/* if ( $the_content = preg_split( '#\s#', strip_tags( $draft->post_content ), 11, PREG_SPLIT_NO_EMPTY ) )
					$item .= '<p>' . join( ' ', array_slice( $the_content, 0, 10 ) ) . ( 10 < count( $the_content ) ? '&hellip;' : '' ) . '</p>'; */// 082
				$list[] = $item;
			}
	?>
		<ul>
			<li><?php echo join( "</li>\n<li>", $list ); ?></li>
		</ul>
		<!-- <p class="textright"><a href="edit.php?post_status=draft" ><?php _e('View all'); ?></a></p> 082 -->
	<?php
		} else {
			echo '<p>' . __('There are no drafts at the moment') . '</p>';
		}
		echo '</div>';
	}
}

Press_This_Reloaded_Mod::init();

