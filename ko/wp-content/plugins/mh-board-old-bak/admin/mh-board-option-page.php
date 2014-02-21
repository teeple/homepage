<?php
add_action( 'admin_menu', 'mh_board_menu' );
function mh_board_menu(){
	add_submenu_page( 'edit.php?post_type=board', 'mh-board-option', __('MH Board Style','mhboard'), 'manage_options', 'mh-board-style', 'mh_board_style' );
	add_submenu_page( 'edit.php?post_type=board', 'mh-board-option', __('MH Board Settings','mhboard'), 'manage_options', 'mh-board-setting', 'mh_board_settings' );
	add_submenu_page( 'edit.php?post_type=board', 'mh-board-option', __('MH Board Permalink','mhboard'), 'manage_options', 'mh-board-permalink', 'mh_board_permalink' );
	add_submenu_page( 'edit.php?post_type=board', 'mh-board-option', __('MH Board Custom CSS','mhboard'), 'manage_options', 'mh-board-css', 'mh_board_css' );
	add_submenu_page( 'edit.php?post_type=board', 'mh-board-option', __('MH Board Update','mhboard'), 'manage_options', 'mh-board-update', 'mh_board_update' );
	$mh_board_permission = add_submenu_page( 'edit.php?post_type=board', 'mh-board-option', __('MH Board Permission','mhboard'), 'manage_options', 'mh-board-permission', 'mh_board_permission' );
	add_action('admin_print_styles-'.$mh_board_permission,'mh_board_admin_styles');
}

/* mh board admin style */

function mh_board_admin_styles(){
	wp_register_style('mh-board-admin-style', plugins_url('/mh-board-admin-style.css', __FILE__),'','0.1' );
	wp_enqueue_style('mh-board-admin-style');
}

add_action('admin_init','mh_board_style_options');
function mh_board_style_options(){
	register_setting( 'mh-board-style-options', 'mh_board_style_options' );
}
function mh_board_style(){
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2>MH Board Style</h2>
	<p class="ssamture_net" style="text-align:right">
	<a href="http://ssamture.net"><img src="http://ssamture.net/ssamturenet.png" border="0"></a>
	</p>
	<form method="post" action="options.php">
		<?php settings_fields( 'mh-board-style-options' ); ?>
		<?php $mh_board_style_options = get_option('mh_board_style_options');
		$button_background = $mh_board_style_options['button_background'];
		$button_color = $mh_board_style_options['button_color'];
		?>
		<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="button_background"><?php echo __('Button background color','mhboard');?></label></th>
				<td>Color code: <input name="mh_board_style_options[button_background]" type="text" id="button_background" value="<?php echo $button_background;?>" size="7" maxlength="7">(* <?php echo __('Specifies the background color of the button.' ,'mhboard');?>(ex:#333333))</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="button_color"><?php echo __('Button font color','mhboard');?></label></th>
				<td>Color code: <input name="mh_board_style_options[button_color]" type="text" id="button_color" value="<?php echo $button_color;?>" size="7" maxlength="7">(* <?php echo __('Specifies the background color of the font.' ,'mhboard');?>(ex:#333333))</td>
			</tr>
		</tbody>
		</table>
		<?php submit_button();?>
	</form>
</div>
<?php
}
add_action('admin_init','mh_board_register_options');
function mh_board_register_options(){
	register_setting( 'mh-board-options', 'mh_board_options' );
}

function mh_board_settings(){
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2><?php echo __('MH Board Settings','mhboard');?></h2>
	<p class="ssamture_net" style="text-align:right">
	<a href="http://ssamture.net"><img src="http://ssamture.net/ssamturenet.png" border="0"></a>
	</p>
	<form method="post" action="options.php">
		<?php settings_fields( 'mh-board-options' ); ?>
		<?php $mh_board_options = get_option('mh_board_options');
		$emailpush = empty($mh_board_options['emailpush']) ? '' : $mh_board_options['emailpush'];
		$mh_comment = empty($mh_board_options['mh_comment']) ? '' : $mh_board_options['mh_comment'];
		$mh_link = empty($mh_board_options['mh_link']) ? '' : $mh_board_options['mh_link'];
		$mh_guestwrite = empty($mh_board_options['mh_guestwrite']) ? '' : $mh_board_options['mh_guestwrite'];
		$mh_category = empty($mh_board_options['mh_category']) ? '' : $mh_board_options['mh_category'];
		$mh_replypost = empty($mh_board_options['mh_replypost']) ? '' : $mh_board_options['mh_replypost'];
		$mh_posts_per_page = empty($mh_board_options['mh_posts_per_page']) ? '10' : $mh_board_options['mh_posts_per_page'];
		
		if($mh_link == 1){
			delete_option('mh_board_write_link');				
			delete_option('mh_board_edit_link');
		}
		?>
		<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="emailpush"><?php echo __('Email Push', 'mhboard');?></label></th>
				<td><?php echo __('Used:', 'mhboard');?> <input name="mh_board_options[emailpush]" type="checkbox" id="emailpush" value="push" <?php if($emailpush == 'push'){echo " checked";}?>>(* <?php echo __('When people leave comments to the author, we will notify you by email.','mhboard');?>)</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="emailpush"><?php echo __('MH Board Comment', 'mhboard');?></label></th>
				<td><?php echo __('Used:', 'mhboard');?> <input name="mh_board_options[mh_comment]" type="checkbox" id="mh_comment" value="1" <?php if($mh_comment == '1'){echo " checked";}?>>(* <?php echo __('Use the comments in the MH Board template.','mhboard');?>)</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mh_guestwrite"><?php echo __('Guest writing', 'mhboard');?></label></th>
				<td><?php echo __('Used:', 'mhboard');?> <input name="mh_board_options[mh_guestwrite]" type="checkbox" id="mh_guestwrite" value="1" <?php if($mh_guestwrite == '1'){echo " checked";}?>>(* <?php echo __('To be a guest writing.','mhboard');?>)</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mh_category"><?php echo __('Categories Hide', 'mhboard');?></label></th>
				<td><?php echo __('Used:', 'mhboard');?> <input name="mh_board_options[mh_category]" type="checkbox" id="mh_category" value="1" <?php if($mh_category == '1'){echo " checked";}?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mh_replypost"><?php echo __('Use reply', 'mhboard');?></label></th>
				<td><?php echo __('Used:', 'mhboard');?> <input name="mh_board_options[mh_replypost]" type="checkbox" id="mh_replypost" value="1" <?php if($mh_replypost == '1'){echo " checked";}?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="posts_per_page"><?php echo __('Posts per page', 'mhboard');?></label></th>
				<td><?php echo __('Used:', 'mhboard');?> <input name="mh_board_options[mh_posts_per_page]" type="text" id="mh_posts_per_page" value="<?php echo $mh_posts_per_page;?>">(* <?php echo __('Allows you to specify the number to be displayed per page.','mhboard');?>)</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="posts_per_page"><?php echo __('Default Category', 'mhboard');?></label></th>
				<td>
					<select name="mh_board_options[mh_default_category]">
						<?php
							$mh_default_category = $mh_board_options['mh_default_category'];
							$board_cats = get_terms('board_cat',array('hide_empty'=>0));
						foreach($board_cats as $board_cat):?>
						<option value="<?php echo $board_cat->term_id;?>"<?php if($mh_default_category == $board_cat->term_id){echo " selected";}?>>
							<?php echo $board_cat->name;?>
						</option>
						<?php endforeach;?>
					</select>
				(* <?php echo __('Allows you to specify the number to be displayed per page.','mhboard');?>)</td>
			</tr>
		</tbody>
		</table>
		<?php submit_button();?>
	</form>
</div>
<?php
}
function mh_board_update(){
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2><?php echo __('MH Board Update','mhboard');?></h2>
	<p class="ssamture_net" style="text-align:right">
	<a href="http://ssamture.net"><img src="http://ssamture.net/ssamturenet.png" border="0"></a>
	</p>
	<?php
	if (function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, MH_BOARD_UPDATE_URL);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');
		$data = curl_exec($ch);
		$data = simplexml_load_string($data);
		
		curl_close($ch);
	} else {
		// curl library is not installed so we better use something else
		$xml = wp_remote_get(MH_BOARD_UPDATE_URL);
		$data = simplexml_load_string($xml['body']);
	}

	if($data->version != MH_BOARD_VERSION){
		echo "현재 MH Board의 버전은 ".MH_BOARD_VERSION."이며 버전 {$data->version} 가 새로 배포되었습니다.<br>";
		echo "다운로드 받으러 가기: <a href='{$data->download}'>$data->download</a>";
	}else{
		echo "현재 MH Board의 버전은 ".MH_BOARD_VERSION."으로 최신버전입니다.";
	}
	?>
	
</div>
<?php	
}
require_once(dirname(__FILE__).'/mh-board-permalink.php');
require_once(dirname(__FILE__).'/mh-board-custom-css.php');
require_once(dirname(__FILE__).'/mh-board-permission.php');

?>