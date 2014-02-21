<?php
/*
Plugin Name: Download Manager(Mod)
Plugin URI: http://guff.szub.net/plugins/
Description: Restrict and track downloads through WordPress. Supports international file name(needs mbstring module). modified by <a href="http://082net.com/">082net</a>
Version: R1.beta2_WP2(mod7)
Author: Kaf Oseo
Author URI: http://szub.net
*//*
	Copyright (c) 2005, Kaf Oseo (http://szub.net) & Cheon, Young-Min (http://082net.com)
	Download Manager is released under the GPL license
	http://www.gnu.org/licenses/gpl.txt

	This is a WordPress plugin (http://wordpress.org).

*//*
Change Log :
mod6 :
	Fixed user level checking on WP 2.x
mod6 :
	removed slimstat track option.
mod5 :
	some small fixes.
	added international file name supports.
mod4 :
	css and javascript for virtual file.
	make it class.
mod3 :
	changed default track plugin from SlimStat-Ex to Download-Manager
mod2 : 
	show filetype icons.
	changed folder name to 'x-download-mgr' to work with Wp-SlimStat-Ex.

*//*
BASIC INSTRUCTIONS
Place download-mgr.php in the plugins directory, and activate under
Plugins in WordPress. To configure, go to Manage > Downloads.

Link to any file in your download directory using the following URL
query format:

	?dl=filename.ext

Examples:

	/blog/index.php?dl=test-program.zip
	http://www.example.com/?dl=RedSox_2004_stats.sxc

*/
/* Added by 082net 

 #Use <file> [file] Tag in Post ::
<file>file1.zip</file> or [file]file1.zip[/file]

#Examples:
<file>download-this.zip</file> or  [file]download-this.zip[/file]  
<file>file1.zip, file2.zip, file3.zip</file> (comma-separated-list)
<file>test/file1.zip</file> (support files in subfolder of 'Download Path(url)'

*/
// user-configurable variable >
// Set $this->tracking_type to have Download Manager use a single table to
// track downloads, multiple tables (one per blog), or do not track.
// Valid value: 'single', 'multiple', 'none'

if(!class_exists('WP_DMGR')) :
class WP_DMGR {
	var $tracking_type = 'multiple';
	var $htmltagset = false;
	var $pluginURL, $conf, $failure_title;

	function WP_DMGR() {
		$basedir = dirname($this->plugin_basename(__FILE__));
		$this->pluginURL = get_option('siteurl') . '/wp-content/plugins/'.$basedir;
		load_plugin_textdomain('download-mgr', 'wp-content/plugins/'.$basedir.'/lang', $basedir.'/lang'); // plugin localization
		$this->conf = get_option('download_mgr');
		$this->conf['path'] = $this->fix_slash(stripslashes($this->conf['path']));
		$this->failure_title = __('Failed to download', 'download-mgr');
		global $wpdb;
		if('multiple' == $this->tracking_type)
			$wpdb->downloads = $wpdb->prefix . 'downloads';
		else
			$wpdb->downloads = 'downloads';
	}

	function set_options() {
		$option['path'] = ABSPATH . 'wp-content/uploads';
//		$option['url'] = get_option('siteurl') . '/wp-content/uploads';
		$option['allowed_level'] = '0';
		$option['show_msgs'] = 1;
		$option['wrong_level_msg'] = __('Sorry, you don\'t have the right user level for downloads.', 'download-mgr');
		$option['no_login_msg'] = __('You must be a ', 'download-mgr') . get_option('blogname') . __(' user and logged on to download.', 'download-mgr');
		add_option('download_mgr', $option);
	}

	function _createTable() {
		 global $wpdb;
			
		$createTable = $wpdb->downloads;
		$myTableStatsQuery = "CREATE TABLE `$createTable` (
			`id` INT UNSIGNED NOT NULL auto_increment,
			`file_name` VARCHAR(255) NOT NULL default '',
			`login` VARCHAR(60) default '',
			`referer` TEXT default '',
			`remote_addr` VARCHAR(40) default '',
			`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY file_name (`file_name`) )";

		// Check if table is already there
		foreach ($wpdb->get_col("SHOW TABLES", 0) as $table ) {
			if ($table == $createTable) {
				return false;
			}
		}
		// Ok, let's proceed
		if ( $wpdb->query( $myTableStatsQuery ) === false ) {
			return false;
		}
		// Just to be sure, we check that tables were actually created
		foreach ( $wpdb->get_col("SHOW TABLES", 0) as $table ) {
			if ( $table == $createTable ) {
				return true;
			}
		}	
		return false;
	}

	function setup() {
		if(!get_option('download_mgr')) {
			$this->set_options();
		}
		$this->_createTable();
	}

	function admin_head() {
?>
	<style type="text/css">
	a.list_file {
		display: block;
		border-bottom: none;
	}
	a:hover.list_file {
		color: #fff;
		background-color: #69c;
	}
	table#downloads {
		width: 100%;
		border-collapse: collapse;
	}
	#downloads th, #downloads td {
		font-size: 81.5%;
		margin: 0;
		padding: 4px;
		border: 2px solid #fff;
	}
	#downloads th {
		font-weight: bold;
	}
	#downloads th#count, #downloads th#login, #downloads th#filename, #downloads th#referer, #downloads th#ip {
		width: auto;
	}
	#downloads th#timestamp {
		width: 140px;
	}
	#downloads td div {
		white-space: nowrap;
		overflow: hidden;
	}
	.download_view a {
		float: right;
		top: 10px;
		right: 10px;
		font-size: .8em;
		text-decoration: none;
		display: block;
		padding: 1px 4px 1px 4px;
		border: none;
	}
	.download_view a:hover {
		color: #fff;
		background-color: #69c;
	}
	</style>
	<script type="text/javascript">//<![CDATA[
	function toggle(id) {
		if (document.getElementById) {
			var tags = document.getElementById(id);
			tags.style.display = (tags.style.display == 'none') ? 'block' : 'none';
		}
	}//]]>
	</script>
<?php
	}

	function wp_head() {
		echo '
		<!--added by download-manager -->
		<link rel="stylesheet" href="'.$this->pluginURL.'/dmgr.css" type="text/css" media="screen" />
		';
	}

	function manager_page() {
		$levels = array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0, 'public');
		$intervals = array(0, 15, 30, 60, 90, 120, 180, 300);
		$test_download_option = $option;
		$button_text = __('Update Settings', 'download-mgr');
		$option = get_option('download_mgr');
		if(isset($_POST['Submit'])) {
			$option['path'] = trailingslashit(trim($_POST['path']));
//			$option['url'] = trailingslashit(trim($_POST['url']));
			$option['allowed_level'] = (int) $_POST['allowed_level'];
			$option['show_msgs'] = (int) $_POST['show_msgs'];
			$option['wrong_level_msg'] = $_POST['wrong_level_msg'];
			$option['no_login_msg'] = $_POST['no_login_msg'];
			$option['show_track'] = (int) $_POST['show_track'];
			$option['show_count'] = (int) $_POST['show_count'];
			$option['block_bots'] = (int) $_POST['block_bots'];
			$option['dl_interval'] = (int) $_POST['dl_interval'];
			$option['ignore_admin_dl'] = (int) $_POST['ignore_admin_dl'];
			update_option('download_mgr', $option);
			echo '<div class="updated"><p>'.__('Options saved.').'</p></div>';
		}
		$option['path'] = stripslashes(htmlspecialchars($option['path']));
//		$option['url'] = stripslashes(htmlspecialchars($option['url']));
		$option['wrong_level_msg'] = stripslashes(htmlspecialchars($option['wrong_level_msg']));
		$option['no_login_msg'] = stripslashes(htmlspecialchars($option['no_login_msg']));
?>
	<div class="wrap">
	<?php
		if('none' != $this->tracking_type) {
			if(!isset($_GET['tracking'])) { 
?>
	<div class="download_view"><a href="?page=x-download-manager&amp;tracking"><?php _e('Tracking Only', 'download-mgr'); ?></a></div>
<?php
			} else { 
?>
	<div class="download_view"><a href="?page=x-download-manager"><?php _e('Show Settings', 'download-mgr'); ?></a></div>
<?php 
			}
		}
?>
	<h2><?php _e('Download Manager', 'download-mgr'); ?></h2>
<?php
			if(!isset($_GET['tracking'])) {
?>
	<form name="download_options" method="post" action="">
		<input type="hidden" name="action" value="update" />
		<fieldset class="options">
		<legend><?php _e('Settings', 'download-mgr'); ?></legend>
		<table cellspacing="2" cellpadding="5" class="editform">
			<tr valign="baseline">
				<th scope="row"><label for="path"><?php _e('Download path:', 'download-mgr') ?></label></th>
				<td><input type="text" name="path" id="path"  size="64" value="<?php echo $option['path']; ?>" /></td>
			</tr><!--
			<tr valign="baseline">
				<th scope="row"><label for="url"><?php //_e('Download URL:', 'download-mgr') ?></label></th>
				<td><input type="text" name="url" id="url"  size="64" value="<?php //echo $option['url']; ?>" /></td>
			</tr> -->
			<tr valign="baseline">
				<th scope="row"><?php _e('Admin download:', 'download-mgr'); ?></th>
				<td><input type="checkbox" name="ignore_admin_dl" id="ignore_admin_dl" value="1"<?php checked(true, $option['ignore_admin_dl']); ?> />
				<label for="ignore_admin_dl"><?php _e('( Do not track admin download )', 'download-mgr'); ?></label></td>
			</tr>
			<tr valign="baseline">
				<th scope="row"><?php _e('Show Counts:', 'download-mgr'); ?></th>
				<td><input type="checkbox" name="show_count" id="show_count" value="1"<?php checked(true, $option['show_count']); ?> />
				<label for="show_count"><?php _e('( Show download count )', 'download-mgr'); ?></label></td>
			</tr>
			<tr valign="baseline">
				<th scope="row"><?php _e('Disallow bots:', 'download-mgr'); ?></th>
				<td><input type="checkbox" name="block_bots" id="block_bots" value="1"<?php checked(true, $option['block_bots']); ?> />
				<label for="block_bots"><?php _e('( Prevent some crawler\'s download )', 'download-mgr'); ?></label></td>
			</tr>
			<tr valign="baseline">
				<th scope="row"><label for="dl_interval"><?php _e('Download interval:', 'download-mgr'); ?></label></th>
				<td><select name="dl_interval" id="dl_interval">
				<?php foreach($intervals as $interval) { ?>
				<option value="<?php echo $interval; ?>"<?php if($option['dl_interval'] == $interval) echo ' selected="selected"'; ?>><?php echo $interval; ?> <?php _e('sec', 'download-mgr'); ?></option>
				<?php } ?></select>
				<?php _e('( Select download interval for one file per remote ip. )', 'download-mgr'); ?></td>
			</tr>
<!-- <?php if(class_exists('wp_slimstat_ex')) { ?>
			<tr valign="baseline">
				<th scope="row"><?php _e('SlimStat:', 'download-mgr'); ?></th>
				<td><input type="checkbox" name="show_track" id="show_track" value="1"<?php checked(true, $option['show_track']); ?> />
				<label for="show_track"><?php _e('(If checked, both Wp-SlimStat-Ex and Download-Manager will track download. default: Download-Manager only)', 'download-mgr'); ?></label></td>
			</tr>
<?php } ?> -->
			<tr valign="baseline">
				<th scope="row"><label for="allowed_level"><?php _e('Access level:', 'download-mgr'); ?></label></th>
				<td><select name="allowed_level" id="allowed_level">
				<?php
				foreach($levels as $level) : ?>
	<option value="<?php echo $level; ?>"<?php if($level == $option['allowed_level']) { echo ' selected="selected"'; } ?>><?php echo $level; ?></option>
<?php endforeach; ?>
				</select>
				<?php _e('(Set lowest allowed user level)', 'download-mgr'); ?>
				</td>
			</tr>
			<tr valign="baseline"<?php if('public' == $option['allowed_level']) echo 'style="display: none;"'; ?>>
			<th scope="row"><label for="show_msgs"><?php _e('Report errors:', 'download-mgr') ?></label></th>
			<td scope="row">
			<label for="show_msgs">
			<input name="show_msgs" type="checkbox" id="show_msgs" value="1" <?php checked('1', $option['show_msgs'], show_msgs); ?> onclick="toggle('msgs')" />&nbsp;
			<?php _e('(Display messages on error)', 'download-mgr') ?>
			</label>
			</td>
			</tr>
		</table>
		<div id="msgs" <?php if('public' == $option['allowed_level'] || false == $option['show_msgs']) { echo 'style="display:none;"'; } ?>>
			<fieldset class="options">
			<legend><?php _e('Error Messages', 'download-mgr'); ?></legend>
			<table cellspacing="2" cellpadding="5" class="editform">
			<tr valign="baseline">
				<th scope="row"><label for="wrong_level_msg"><?php _e('Wrong level:', 'download-mgr') ?></label></th>
				<td><input type="text" name="wrong_level_msg" id="wrong_level_msg"  size="75" value="<?php echo $option['wrong_level_msg']; ?>" /><br/>
			</tr>
			<tr valign="baseline">
				<th scope="row"><label for="no_login_msg"><?php _e('No login:', 'download-mgr') ?></label></th>
				<td><input type="text" name="no_login_msg" id="no_login_msg"  size="75" value="<?php echo $option['no_login_msg']; ?>" /><br/>
			</tr>
			</table>
			</fieldset>
		</div>
		</fieldset>
	<p class="submit">
	<input type="submit" name="Submit"
	value="<?php echo $button_text; ?> &raquo;" />
	</p>
	</form>
<?php
		}
		if('none' != $this->tracking_type) {
			$track_file = '';
//			$this->_table('create');
			if(isset($_GET['tracking']) && !empty($_GET['tracking'])) {
				$track_file = $_GET['tracking'];
			}
?>
		<fieldset class="options">
		<legend><?php _e('Download Tracking', 'download-mgr'); ?><?php if($track_file) echo ': ' . basename($track_file) . ' [<a href="?page=x-download-manager&amp;tracking">&laquo;</a>]';   ?></legend>
		<table id="downloads">
<?php if($track_file !== '') $this->_table('list_file', $_GET['tracking']); else $this->_table('list', $option['allowed_level']) ?>
		</table>
		</fieldset>
	<?php	
		} 
		echo '</div>';
	}// end of manager page


	function add_page() {
		if(function_exists('add_management_page')) {
			add_management_page(__('Download Manager', 'download-mgr'), __('Downloads', 'download-mgr'), 'manage_options', 'x-download-manager', array(&$this, 'manager_page'));
		}
	}

	function _table($do='', $download_param='') {
		global $wpdb;
		$option = get_option('download_mgr');
		$show_table = false;

		if('none' != $this->tracking_type) {

			switch($do) :
				case 'insert' :
					$result = $wpdb->query(
					"INSERT INTO $wpdb->downloads (id, file_name, login, referer, remote_addr, date) VALUES(NULL, '" .
					$download_param[0] . "', '" .
					$download_param[1] . "', '" .
					$download_param[2] . "', '" .
					$download_param[3] . "', '" .
					$download_param[4] . "')" );
					return $result;
				break;
				case 'list' :
					$file_names = @$wpdb->get_col("SELECT DISTINCT file_name FROM $wpdb->downloads ORDER BY file_name ASC");
					if($file_names) {
						if('public' != $option['allowed_level']) {
							$login = "<th id=\"dl_login\">login</th>";
						}
						$download_list = "<tr>\n<th id=\"filename\">filename</th>$login<th id=\"referer\">referer</th><th id=\"ip\">ip</th><th id=\"timestamp\">timestamp</th><th id=\"count\">count</th>\n</tr>";
						foreach($file_names as $file_name) {
							$class = ('alternate' == $class) ? '' : 'alternate';
							$download = @$wpdb->get_row("SELECT * FROM $wpdb->downloads WHERE file_name = '$file_name' ORDER BY date DESC");
							$download_count = count($wpdb->get_col("SELECT id FROM $wpdb->downloads WHERE file_name = '$file_name'"));
							if('public' != $option['allowed_level']) {
								$download->login = ($download->login == '') ? '&nbsp;' : $download->login;
								$td_login = "<td><div>$download->login</div></td>";
							}
							$download->referer = wp_specialchars($this->strcut($download->referer, 64));
							$real_filename = basename($download->file_name);
							$download->remote_addr = (!empty($download->remote_addr)) ? $download->remote_addr : 'Unknown';
							$download->referer = (!empty($download->referer)) ? $download->referer :'Unknown';
							$download_list .= "<tr valign=\"top\" class=\"$class\">\n\t";
							$download_list .= "<td><span>";
							$download_list .= "<a href=\"?page=x-download-manager&amp;tracking=$download->file_name\" ";
							$download_list .= "class=\"list_file\" title=\"$download->file_name ($download_count)\">$real_filename</a>";
							$download_list .= "</span></td>";
							$download_list .= "$td_login\n\t";
							$download_list .= "<td><span>$download->referer</span></td>\n\t";
							$download_list .= "<td><span>$download->remote_addr</span></td>\n\t";
							$download_list .= "<td><span>$download->date</span></td>\n\t";
							$download_list .= "<td style=\"text-align:right;\"><span>$download_count</span></td>\n";
							$download_list .= "</tr>";
						}
					} else {
						$download_list = "<tr>\n<th>" . __('No downloads have been tracked.', 'download-mgr') . "</th>\n</tr>";
					}
					echo $download_list;
				break;
				case 'list_file' :
					$downloads = @$wpdb->get_results("SELECT login, referer, remote_addr, date FROM $wpdb->downloads WHERE file_name = '$download_param' ORDER BY date DESC LIMIT 0,40");
					if($downloads) {
						if('public' != $option['allowed_level']) {
							$login = "<th id=\"dl_login\">login</th>";
						}
						$download_list = "<tr>\n$login<th id=\"referer\">referer</th><th id=\"ip\">ip(country)</th><th id=\"timestamp\">timestamp</th>\n</tr>";
						foreach($downloads as $download) {
							$class = ('alternate' == $class) ? '' : 'alternate';
							if('public' != $option['allowed_level']) {
								$download->login = ($download->login == '') ? '&nbsp;' : $download->login;
								$td_login = "<td><div>$download->login</div></td>";
							}
							$download->referer = wp_specialchars($this->strcut($download->referer, 64));
							$download->remote_addr = (!empty($download->remote_addr)) ? $download->remote_addr : 'Unknown';
							$download->referer = (!empty($download->referer)) ? $download->referer :'Unknown';
							$download_list .= "<tr valign=\"top\" class=\"$class\">\n$td_login<td><div>$download->referer</div></td><td><div>$download->remote_addr</div></td><td><div>$download->date</div></td>\n</tr>";
						}
					} else {
						$download_list = "<tr>\n<th>" . __('No downloads have been tracked.', 'download-mgr') . "</th>\n</tr>";
					}
					echo $download_list;
				break;
			endswitch;
		}
	}

	function strcut($text, $len=60) {
		if( strlen($text) > $len ) {
			if(function_exists('mb_strcut')) 
				$text = mb_strcut($text, 0, $len, get_option('blog_charset'));
			else
				$text = substr($text, 0, $len);
			$text .= "...";
		}
		return $text;
	}

	function _die($message, $title='', $status=500) {
		global $wp_locale, $wp_version;

		if( !headers_sent() ) {
			if (function_exists('status_header')) status_header( $status );
			if (function_exists('nocache_headers')) nocache_headers();
			header( 'Content-Type: text/html; charset=utf-8' );
		}
		if ( empty($title) ) {
			if ( function_exists( '__' ) )
				$title = __( 'WordPress &rsaquo; Error' );
			else
				$title = 'WordPress &rsaquo; Error';
		}
		$admin_dir = get_option('siteurl').'/wp-admin/';
		$admin_css_dir = $wp_version < '2.3' ? $admin_dir : $admin_dir . 'css/';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists( 'language_attributes' ) ) language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $title ?></title>
	<link rel="stylesheet" href="<?php echo $admin_css_dir; ?>install.css" type="text/css" />
<?php
if ( ( $wp_locale ) && ( 'rtl' == $wp_locale->text_direction ) ) : ?>
	<link rel="stylesheet" href="<?php echo $admin_css_dir; ?>install-rtl.css" type="text/css" />
<?php endif; ?>
</head>
<body id="error-page">
		<h1 id="logo"><img alt="WordPress" src="<?php echo $admin_dir; ?>images/wordpress-logo.png" /></h1>
		<p><?php echo $message; ?></p>
</body>
</html>
<?php
		die();
	}

	function check_user($level) {
		global $user_level;
		if( 'public' == $level )
			return true;

		if(function_exists('current_user_can'))
			return current_user_can('level_'.$level);

		get_currentuserinfo();
		return $user_level >= $level;
	}

	function fix_slash($path) {
		$path = str_replace('\\','/',$path); // sanitize for Win32 installs
		$path = preg_replace('|/+|','/', $path); // remove any duplicate slash
		return $path;
	}

	function _basename($file) {
		$basename = array_reverse(explode('/', $file));
		$basename = $basename[0];
		return $basename;
	}

	// To avoid problems with SAFE_MODE, we will not use is_file
	// of file_exists, but a loop through current directory
	function is_file_exists($file) {
		$file_name = $this->_basename($file);
		$file_dir = dirname($file) . '/';
		$subfolder = str_replace($this->conf['path'], '', $file_dir);
		if(!is_dir($file_dir))
			return false;
		$file_exists = false;
		$blog_is_utf8 = strtoupper(get_option('blog_charset')) == 'UTF-8';
		if($dh = opendir($file_dir)) {
			while( ($_file = readdir($dh)) !== false) {
				if($_file == '.' || $_file == '..' )
					continue;
				if($_file == $file_name) {
					$file_exists = $_file;
					break;
				} elseif ($blog_is_utf8 && !seems_utf8($_file)) { // fix for international file name
					if (function_exists('mb_convert_encoding'))
						$_file_c = mb_convert_encoding($_file, 'UTF-8', 'ASCII, UTF-8, EUC-KR, JIS, EUC-JP, SJIS, ISO-8859-1');
					else 
						continue;
					if($_file_c == $file_name) {
						$file_exists = $_file; // return real file name
						break;
					}
				}
			}
			closedir($dh);
		}
		$file_exists = $subfolder . '/' . $file_exists;
		return $file_exists;
	}

	 // if 'dl' GET query, start downloadin'!
	function _dmgr() {
		global $user_login;

		$file_name = urldecode(trim($_GET['dl']));
		// check query string. we need just 'dl'
		// we do not allow url on file name.
		if( sizeof($_GET) > 2 || preg_match('|^https?://|i', $file_name) ) {
			$this->_die(__('Please download from proper sites', 'dmgr'), $this->failure_title);
		}

		// if user used backslashes
		$file_name = $this->fix_slash($file_name);
		// protect from site traversing
		$file_name = str_replace('../', '', $file_name);
		$file_name = trim($file_name, '/');
		if(!$file_name)
			return; // no file specified, so end gracefully

		// check if proper access level
		if ( $this->conf['allowed_level'] && !$this->check_user((int)$this->conf['allowed_level'])) {
			if(!$this->conf['show_msgs'])
				return; // just go to blog home
			if( $user_login ) { // is user but wrong level
				$this->_die( stripslashes($this->conf['wrong_level_msg']), $this->failure_title );
			} else { // is not a user
				$this->_die( stripslashes($this->conf['no_login_msg']), $this->failure_title );
			}
		}
		
		// user info
		$referer = $_SERVER['HTTP_REFERER'];
		$ip = $_SERVER['REMOTE_ADDR'];
		$now = date('Y-m-d H:i:s');

		// block search bots and some crawlers.
		if($this->conf['block_bots']) {
			global $SSTrack;
			$user_agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
			if (is_object($SSTrack) && method_exists($SSTrack, 'is_bot')) {
				$info = $SSTrack->_determineBrowser($user_agent);
				$browser_code = $info['browser'];
				if ( $SSTrack->is_bot( $browser_code, $user_agent, array('bots'=>true, 'feeds'=>true, 'validators'=>true) ) )
					$this->_die( __('Your user_agent(bot) is not allowed to download this file.', 'download-mgr'), $this->failure_title );
			} else {
				$robots = array(
				//downloadtool
				'check&get', 'check&amp;get', 'download_express', 'downloader', 'download ', 'getright', 'flashget', 'scraper', 'webcapture', 'xget', 'webcopier', 'webzip', 'easydl', 'frontpage', 'recoder',
				//validators
				'link valet', 'validity', 'linksmanager', 'mojoo robot', 'validator', 'link system', 'link checker', 'sitebar/', 'checker', 'deadlinkcheck',
				//readers
				'rss-bot', 'rss-spider', 'rss2email', 'reader', 'syndic', 'aggregat', 'subscriber', 'marsedit', 'netvisualize', 'omnipelagos', 'protopage', 'simplepie', 'touchstone',
				//standard bots
				'bot.', 'bot ', 'bot/', 'bot(', 'bot;', 'b-o-t', 'bot@', 'bot)', 'bot-', '-bot', 'robots', 'spider.', 'spider ', 'spider/', 'spider(', 'spider;', 'spider@', 'spider)', 'spider_', ' spider', '-spider', 'spider-', 'spider+', 'get/', 'get(', 'crawl', 'grabber', 'yeti', 'wisenut', 'msnbot', '1noon', 'seeker', 'java ', 'java/', 'fetch', 'collector', 'email ', 'machine', 'wisebot', 'capture', 'scrap', 'daum', 'empas', 'phantom', 'harvest', 'yandex', 'rambler', 'aport', 'naverbot', 'nhnbot', 'altavista', 'wanadoo', 'bbc.', 'alltheweb', 'looksmart', 'gigablast', 'teoma', 'clusty', 'hotbot', 'tesco', 'fantomas', 'godzilla', 'greenbrowser', 'surfer',
				//other bots
				'ask ', 'ask.', 'fast ', 'fast-', 'szukaj', 'boitho', 'envolk', 'galaxy.', 'ingrid', '/dmoz', 'openfind', 'accoona', 'arachmo', 'b-o-t', 'findlinks', 'htdig', 'ia_archiver', 'larbin', 'libwww-perl', 'linkwalker', 'lwp-trivial', 'mabontland', 'mvaclient', 'nicebot', 'oegp', 'pompos', 'pycurl', 'sbider', 'scrubby', 'semanticdiscovery', 'silk/', 'snappy', 'sqworm', 'stackrambler', 'teoma', 'updated', 'voyager', 'vyu2', 'webcollage', 'zao', 'ecollector', 'missigua', 'pussycat', 'psycheclone', 'shockwave', 'application/x-www-form-urlencoded', 'digger', 'jakarta', 'adwords', 'boston project', 'grub', 'hanzoweb', 'indy library', 'murzillo', 'poe-component', 'snoop', 'webster', 'yoono', 'browsex', 'htmlgobble', 'httpcheck', 'httpconnect', 'httpget', 'httrack', 'imagelock', 'incywincy', 'informant', 'carp', 'blogpulse', 'blogssay', 'edgeio', 'pubsub', 'pulpfiction', 'youreadme', 'pluck', 'justview', 'antenna', 'walker', 'sitesucker', 'catch', 'webcopy', 'linker', 'worm', 'jeeves', 'javabee', 'abacho', 'agentname', 'ask', 'automated browscap.ini updater', 'become', 'best whois', 'bookdog', 'bravobrian bstop', 'browscap updater', 'ccubee', 'cjnetworkquality', 'conexcol.com', 'convera', 'cyberspyder link test', 'deepindex', 'depspid', 'directories', 'dlc', 'dns tools', 'domain dossier', 'dtaagent', 'earthcom', 'earthcom', 'eventax', 'excite', 'favorg', 'favorites sweeper', 'filangy', 'galaxy', 'gazz', 'gjk_browser_check', 'hotzonu', 'http/1.0', 'iecheck', 'iltrovatore-setaccio', 'internetlinkagent', 'internetseer', 'isilox', 'ivia project', 'jrtwine', 'keyword density', 'linkalarm', 'linklint', 'linkman', 'lycoris desktop/lx', 'mackster', 'mail.ru', 'medhunt', 'metaspinner/0.01', 'minirank', 'mozdex', 'n-stealth', 'net::trackback', 'netpromoter link utility', 'netvision', 'ocelli', 'octopus', 'omea pro', 'orbiter', 'pagebull', 'poirot', 'poodle predictor', 'popdex', 'powermarks', 'rawgrunt', 'redcell', 'rlinkcheker', 'robozilla', 'sagoo', 'sensis', 'shopwiki', 'shunix', 'singing fish', 'spinne', 'sproose', 'subst?cia', 'supercleaner', 'syncmgr', 'szukacz', 'tagyu', 'tkensaku', 'twingly recon', 'ucmore', 'updatebrowscap', 'urlbase', 'vagabondo', 'vermut', 'vse link tester', 'w3c-webcon', 'walhello', 'webbug', 'weblide', 'webox', 'webtrends', 'webtrends link analyzer', 'whizbang', 'worqmada', 'wotbox', 'xml sitemaps generator', 'xyleme', 'zatka', 'zibb', 'zoneedit failover monitor', 'ogeb', 'www_browser', 
				/* Bad bots */
				'3d-ftp', 'activerefresh', 'amazon.com', 'amico alpha', 'anonymizer', 'anonymizied', 'anonymous', 'artera', 'asptear', 'autohotkey', 'autokrawl', 'automate5', 'b2w', 'backstreet browser', 'basichttp', 'beamer', 'bitbeamer', 'bits', 'bittorrent', 'blocknote.net', 'blue coat systems', 'bluecoat proxysg', 'brand protect', 'ce-preload', 'cerberian', 'cfnetwork', 'changedetection', 'charlotte', 'cherrypickerelite', 'chilkat', 'cobweb', 'coldfusion', 'copyright/plagiarism', 'copyrightcheck', 'custo', 'datacha0s', 'disco pump', 'downloadsession', 'dynamic+', 'e-mail address extractor', 'e-mail siphon', 'easyrider', 'ebingbong', 'emailwolf', 'emeraldshield', 'extractorpro', 'extreme picture finder', 'ezic.com', 'fake ie', 'flatarts favorites icon tool', 'flatland industries', 'forschungsportal', 'franklin locator', 'freshdownload', 'gamespyhttp', 'gnome-vfs', 'go ahead got-it', 'gozilla', 'gozilla', 'hatena', 'hatenascreenshot', 'hcat', 'hiddenmarket', 'hloader', 'holmes', 'hoowwwer', 'html2jpg', 'http generic', 'httperf', 'httpsession', 'httpunit', 'hyperestraier', 'iconsurf', 'inet - eureka app', 'ineturl', 'ineturl', 'intelix', 'internet archive', 'internet ninja', 'internetarchive', 'ip*works', 'ip*works!', 'ipcheck server monitor', 'kapere', 'kevin', 'kretrieve', 'lachesis', 'leechftp', 'lftp', 'libweb/clshttp', 'lightningdownload', 'linkextractorpro', 'linktiger', 'looq', 'lorkyll', 'mailmunky', 'mapoftheinternet', 'metatagsdir', 'mfc foundation class library', 'mfc_tear_sample', 'mfhttpscan', 'microsoft', 'microsoft office existence discovery', 'microsoft url control', 'microsoft-webdav', 'mister pix', 'mono browser capabilities updater', 'moozilla', 'morfeus fucking scanner', 'ms ipp dav', 'ms ipppd', 'ms opd', 'ms proxy', 'myst monitor service', 'myzilla', 'naofavicon4ie', 'net probe', 'net vampire', 'net_vampire', 'netants', 'netcarta_webmapper', 'netmechanic', 'netprospector', 'netpumper', 'netreality', 'netsucker', 'newt activex', 'nextthing.org', 'nozilla/p.n', 'nudelsalat', 'nutch', 'ocn-soc', 'octopodus', 'offline browsers', 'ossproxy', 'pagedown', 'pageload', 'pajaczek', 'panda antivirus titanium', 'panscient.com', 'pavuk', 'peerfactory', 'photostickies', 'picaloader', 'pigblock', 'pingdom', 'pixfinder', 'plinki', 'pmafind', 'pogodak!', 'privoxy', 'proxomitron', 'proxy servers', 'proxytester', 'prozilla', 'python', 'radiation retriever', 'realdownload', 'relevare', 'repomonkey', 'scoutabout', 'secure computing corporation', 'shareaza', 'shelob', 'sherlock', 'showxml', 'silentsurf', 'site monitors', 'siteparser', 'sitesnagger', 'sitewinder', 'smartdownload', 'speed download', 'steeler', 'steroid download', 'sunrise', 'superhttp', 'surfcontrol', 'tarantula', 'teleport', 'texis', 'theophrastus', 'thunderstone', 'trend micro', 'tweakmaster', 'twiceler', 'uoftdb experiment', 'url control', 'url2file', 'urlcheck', 'urly warning', 'vegas95', 'versatel', 'vobsub', 'vortex', 'web magnet', 'webbandit', 'webcheck', 'webclipping.com', 'webcorp', 'webenhancer', 'webgatherer', 'webinator', 'webminer', 'webmon', 'webpatrol', 'webreaper', 'webripper', 'websauger', 'website extractor', 'website quester', 'websnatcher', 'webstripper', 'webwhacker', 'winhttp', 'winscripter inet tools', 'wintools', 'www-mechanize', 'www4mail', 'wwwster', 'xenu\'s link sleuth', 'y!oasis', 'yoow!', 'zeus', '(compatible):', '(compatible; ):'
				);
				foreach ($robots as $bot) {
					if(strpos($user_agent, $bot) !== false || empty($user_agent)) {
						$this->_die( __('Your user_agent(bot) is not allowed to download this file.', 'download-mgr'), $this->failure_title );
					}
				}
			}
		}

		// check download time interval
		if( $this->conf['dl_interval'] > 0 ) {
			global $wpdb;
			$last_time = (int) $wpdb->get_var("SELECT MAX(UNIX_TIMESTAMP(`date`)) AS `dt` FROM $wpdb->downloads WHERE remote_addr = '$ip' AND file_name = '".$wpdb->escape($file_name)."' ", 0, 0);
			if( $last_time && ( time() < $last_time + (int)$this->conf['dl_interval'] ) )
				$this->_die( sprintf(__('You are downloading same file too quickly. Slow down. (Allowed interval : %s sec)', 'download-mgr'), $this->conf['dl_interval'] ), $this->failure_title );
		}

		// check if file is exists
		$is_attachment_id = 0;
		if ( is_numeric($file_name) ) {
			$is_attachment_id = $file_name;
			$file_path = get_attached_file($file_name, true);
			$file_url = wp_get_attachment_url($file_name);
		} else {
			$file_path = $this->conf['path'].$file_name;
			$file_url = $this->get_file_url($file_path);
		}
		$_real_file = $this->is_file_exists($file_path);

		if(!$_real_file) {// provide 404 error and exit
			$this->_die(__('The file you requested is not found on this server.', 'download-mgr'), $this->failure_title, 404);
		}

		// if file is exists...
		if ($file_name != $_real_file)
			$file_path = str_replace($file_name, $_real_file, $file_path);
		// add record info
		if ( !$this->conf['ignore_admin_dl'] || !$this->check_user(8)) {
			$download_array = array($file_name, $user_login, $referer, $ip, $now);
			$this->_table('insert', $download_array);
		}

		if ( !empty($_GET['directdl']) ) {
			wp_redirect($file_url);
			exit;
		}

		// now start downloading.
		@ignore_user_abort();
		@set_time_limit(0);

		if ( !$mimetype = $this->get_mime_type($file_path) ) {
			$mimetype = "application/force-download";
			//$mimetype = 'application/octet-stream';  // set mime-type
		}

		$handle = fopen($file_path, "rb"); // now let's get the file!
		if(!$handle) // if cannot read file.
			return;
		header("Pragma: "); // Leave blank for issues with IE
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: $mimetype");
		header("Content-Disposition: attachment; filename=" . $this->_basename($_real_file));
		header("Content-Length: " . (filesize($file_path)));
		fpassthru($handle);
		die();
	}

	function get_mime_type( $file_path ) { 
		if ( function_exists( 'mime_content_type' ) ) {
			$file_mime_type = @mime_content_type( $file_path );
		} elseif ( function_exists( 'finfo_file' ) ) {
			$finfo = @finfo_open(FILEINFO_MIME);
			$file_mime_type = @finfo_file($finfo, $file_path);
			finfo_close($finfo);  
		} else {
			$file_mime_type = false;
		}
		if ( !$file_mime_type ) {
			$extension = pathinfo( $file_path, PATHINFO_EXTENSION );
			foreach ( get_allowed_mime_types( ) as $exts => $mime ) {
				if ( preg_match( '!^(' . $exts . ')$!i', $extension ) ) {
					$file_mime_type = $mime;
					break;
				}
			}
		}
		return $file_mime_type;	 
	}

	function get_file_url($path) {
		if (isset($_SERVER['HTTPS']) &&
				($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
				isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
				$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$protocol = 'https://';
		}
		else {
			$protocol = 'http://';
		}
		$root = $this->fix_slash($_SERVER['DOCUMENT_ROOT']);
		$path = '/' . ltrim(str_replace($root, '', $path), '/');
		return $protocol . $_SERVER['HTTP_HOST'] . $path;
	}

	//Added by 082net
	function _filter($content) {
		if($this->htmltagset)
			$content = preg_replace('#\<file\>(.*?)\</file\>#sie', '$this->_content(\'$1\');', $content);
		$content = preg_replace('#\[file\](.*?)\[/file\]#sie', '$this->_content(\'$1\');', $content);
		return $content;
	}

	function _content($files) { 
		$files = str_replace(array(' ', "\r\n", "\n"), '', $files);
		$files = explode(',', $files);
		if(empty($files))
			return;		
		$url = get_option('home');
		$r = '<div class="dm-wrap">';
		$count_str = '';
		for ($i = 0; $i < count($files); $i++) {
			$file = trim($files[$i]);
			$file = ltrim($file, '/');
			if('' == $file) continue;
			$fname = $this->_basename($file);
			if($this->conf['show_count']) {
				global $wpdb;
				$query = "SELECT COUNT(*) AS `count` FROM `$wpdb->downloads` WHERE `file_name` LIKE '$file' ";
				if($count = $wpdb->get_var($query)) {
					$count_str = ', '.$count;
				}
			}
			if ( is_numeric($file) ) {
				$f = get_attached_file($file, true);
			} else {
				$f = $this->conf['path'].$file;
			}
			//$f = $this->conf['path'].$file;
			$_fname = $this->is_file_exists($f);
			if ($_fname) {
				$f = str_replace($fname, $_fname, $f);
				$fsize = $this->_getFilesize(@filesize($f));
				$r .= '<div class="dm-file"><span class="dm-fname">';
				$r .= '<img alt="download" src="'.$this->pluginURL.'/i/i-png/'.$this->_getFileicon($fname).'.png" style="vertical-align:top;" /> ';
				$r .= '<a href="'.$url.'/?dl='.urlencode($file).'" title="Download '.$fname.'"> '.$fname.'</a>';
				$r .='</span><span class="dm-meta">('.$fsize.$count_str.')</span></div>';
			} else {
				$r .= '<div class="dm-file">File Not Found</div>';
			}
		}
		$r .= '</div>';
		return $r;
	}

	//from Download Beautifier(http://binslashbash.org)
	function _getFilesize ($fsize) {
		if (strlen($fsize) <= 9 && strlen($fsize) >= 7) {				
			$fsize = number_format($fsize / 1048576,1);
			return "$fsize MB";
		} elseif (strlen($fsize) >= 10) {
			$fsize = number_format($fsize / 1073741824,1);
			return "$fsize GB";
		} else {
			$fsize = number_format($fsize / 1024,1);
			return "$fsize KB";
		}
	}

	//filetype icons by Mark James(http://www.famfamfam.com/)
	function _getFileicon ($file) {
		$iconArray = array('tar'=>'tar', 'gz'=>'tgz', 'zip'=>'zip', 'rar'=>'rar', 'pdf'=>'pdf', 'doc'=>'doc', 'txt'=>'txt', 'c'=>'c', 'cpp'=>'c', 'pl'=>'source', 'py'=>'source', 'phps'=>'php', 'php'=>'php', 'sql'=>'sql', 'mp1'=>'mp3', 'mp2'=>'mp3', 'wma'=>'snd', 'mp3'=>'mp3', 'wav'=>'mp3', 'midi'=>'snd', 'mid'=>'snd', 'snd'=>'snd', 'avi'=>'vid1', 'mpg'=>'vid1', 'mpeg'=>'vid1', 'mov'=>'vid1', 'moov'=>'vid1', 'wmf'=>'vid', 'divx'=>'vid1', 'asf'=>'vid', 'wmv'=>'vid', 'mp4'=>'vid1', 'psd'=>'psd', 'gif'=>'gif', 'jpg'=>'jpg', 'jpeg'=>'jpg', 'tif'=>'tif', 'png'=>'png', 'bmp'=>'bmp', 'tiff'=>'tif', 'html'=>'html', 'htm'=>'html', 'swf'=>'swf');
		$filetype = array_reverse(explode('.', basename($file)));
		if(count($filetype) > 1 )
			$icon = (isset($iconArray[$filetype[0]]))?$iconArray[$filetype[0]]:"default";
		else 
			$icon = 'download';
		return $icon;
	}

	function is_dmgr_page() {
		return isset($_GET['page']) && $_GET['page'] == 'x-download-manager';
	}

	function plugin_basename($file) {
		$wp_plugin_dir = defined('WP_PLUGIN_DIR') ? WP_PLUGIN_DIR : 'wp-content/plugins';
		$file = str_replace('\\','/',$file); // sanitize for Win32 installs
		$file = preg_replace('|/+|','/', $file); // remove any duplicate slash
		$plugin_dir = str_replace('\\','/',$wp_plugin_dir); // sanitize for Win32 installs
		$plugin_dir = preg_replace('|/+|','/', $plugin_dir); // remove any duplicate slash
		$file = preg_replace('|^' . preg_quote($plugin_dir, '|') . '/|','',$file); // get relative path from plugins dir
		return $file;
	}

	function add_query_var($vars) {
		$vars[] = 'dl';
		return $vars;
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new WP_DMGR();
		}
		return $instance[0];
	}

}// end of class
endif;//if !class_exists

$WP_DMGR =& WP_DMGR::get_instance();
if($wp_version < '2') {
	if (isset($_GET['activate']) && $_GET['activate'] == 'true')
		add_action( 'init', array( &$WP_DMGR, 'setup' ) );
} else {
	add_action('activate_'.$WP_DMGR->plugin_basename(__FILE__), array(&$WP_DMGR, 'setup'));
}

if(isset($_GET['dl']) && !empty($_GET['dl']))
	add_action('init', array(&$WP_DMGR, '_dmgr'), 0);
add_action('wp_head', array(&$WP_DMGR, 'wp_head'));
add_filter('the_content', array(&$WP_DMGR, '_filter'), 9);
add_filter('the_excerpt', array(&$WP_DMGR, '_filter'), 9);
add_action('admin_menu', array(&$WP_DMGR, 'add_page'));
//add_filter('query_vars', array(&$WP_DMGR, 'add_query_var'));

if ($WP_DMGR->is_dmgr_page()) {
	add_action('admin_head', array(&$WP_DMGR, 'admin_head'));
}

unset($WP_DMGR);
?>