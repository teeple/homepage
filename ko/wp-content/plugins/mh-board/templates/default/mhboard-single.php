<?php 
global $mh_board,$category,$mh_board_options;
do_action('mh_board_read_permission');
if(!$mh_board_options['permission']){
	echo __('Access Denied','mhboard');
	return false;
}
if ( is_singular() ) wp_enqueue_script( 'comment-reply' );
?>
<div id="mh-board">
<?php mhb_get_template_part( 'mhboard','category-menu');?>
<table cellpadding="0" cellspacing="0" class="board view">
<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php $category =@ wp_get_object_terms(get_the_ID(),'board_cat');?>
		<?php
			$action = '';
			$author = get_the_author();
			if($author){
				$user_data = get_userdata(get_the_author_meta('ID'));
				$site = $user_data->user_url;
				$email = $user_data->user_email;
			}else{
				$guest_info = get_post_meta(get_the_ID(),'guest_info',true);
				$author = $guest_info['guest_name'];
				$action = 'guest';
				$email = $guest_info['guest_email'];
				$site = $guest_info['guest_site'];
			}				
		?>
		<tr class="thead">
			<th><?php echo __('Title' ,'mhboard');?></th><td colspan="5"><?php the_title();?></td>
		</tr>
		<tr>
			<th><?php echo __('Author' ,'mhboard');?></th><td><?php echo $author;?><?php if($site){?>(<a href="http://<?php echo $site;?>"><?php echo "http://".$site;?></a>)<?php }?></td><th><?php echo __('Count' ,'mhboard');?></th><td><?php echo $mh_board->get_count(get_the_ID());?></td><th><?php echo __('Date' ,'mhboard');?></th><td><?php echo get_the_date('Y/m/d');?></td>
		</tr>
		<?php
		$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post->ID );
		$attachments = get_posts($args);
		if($attachments){
		?>
		<tr>
			<th><?php echo __('Attachment' ,'mhboard');?></th>
			<td colspan="5" class="attachment_name">
		<?php
		}
		foreach($attachments as $attachment){
		?>
		<a href="<?php echo $attachment->guid;?>" target="_blank"><?php echo $attachment->post_title;?></a><br/>
		<?php
		}
		if($attachments){
		?>
			</td>
		</tr>
		<?php			
		}
		?>
		<tr>
			<td colspan="6" class='content'>
			<?php if(current_user_can('administrator') && isset($post->post_password)):?>
				<?php echo nl2br($post->post_content);?>
			<?php else :?>
				<?php the_content();?></td>
			<?php endif;?>
		</tr>
		<tr>
			<th><?php echo __('Tags' ,'mhboard');?></th>
			<td colspan="5"><?php the_tags('',',','');?></td>
		</tr>
		
	<?php  endwhile; ?>
<?php endif;
?>
	
</table>
<div class="copyright">
	<a href="http://ssamture.net"><img src="http://ssamture.net/ssamturenet.png" border="0"/></a>
</div>
<div class="action clearfix">
	<?php if(is_admin()):?>
		<a href="<?php echo mh_get_board_write_link();?>" class="button"><?php echo __('Write' ,'mhboard');?></a>
	<?php endif;?>
	<?php if(is_user_logged_in() && get_current_user_id() == get_the_author_meta('ID')):?>
		<form action="<?php echo mh_get_board_edit_link();?>" method="post"><input type="hidden" name="post_id" value="<?php the_ID();?>"/>
			<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
			<input type="submit" class="button" value="<?php echo __('Edit' ,'mhboard');?>"/>
		</form>
		<form action="<?php echo mh_get_board_edit_link();?>" method="post" id="delete_board"><input type="hidden" name="post_id" value="<?php the_ID();?>"/>
			<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
			<input type="hidden" name="action" value="delete"/>
			<input type="submit" class="button" value="<?php echo __('Delete' ,'mhboard');?>"/>
		</form>
	<?php elseif($action == 'guest'):?>
		<form action="<?php echo mh_get_board_edit_link();?>" method="post"><input type="hidden" name="post_id" value="<?php the_ID();?>"/>
			<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
			<input type="submit" class="button" value="<?php echo __('Edit' ,'mhboard');?>"/>
		</form>
		<form action="<?php echo mh_get_board_edit_link();?>" method="post" id="delete_board"><input type="hidden" name="post_id" value="<?php the_ID();?>"/>
			<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
			<input type="hidden" name="action" value="delete"/>
			<input type="hidden" name="edit_type" value="guest"/>
			<input type="submit" class="button" value="<?php echo __('Delete','mhboard');?>"/>
		</form>
		<!--<div id="popup" class="clearfix">
			<div id="popupheader">
				<h5>수정 비밀번호</h5>
			</div>
			<div class="popupcontent">
				<input type="hidden" name="post_id" value="<?php the_ID();?>"/>
				<input type="hidden" name="action" value="delete"/>
				<input type="password" name="guest_password"/>
				<input type="submit" class="button" value="삭제"/>
			</div>
		</div>-->
	<?php endif;?>
	<?php if($post->post_parent == 0 && @$mh_board_options['mh_replypost'] == 1):?>
		<form action="<?php echo mh_get_board_write_link(get_the_ID());?>" method="post">
			<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
			<input type="submit" class="button" value="<?php echo __('Reply' ,'mhboard');?>"/>
		</form>
	<?php endif;?>
	<input type="button" name="mh-list" value="<?php echo __('List' ,'mhboard');?>" class='button mh-list' id="mh-cancel"/>
</div>
<div class="pagenavi">
<?php
mh_pagenavi();
?>
</div>
<?php 

$mh_comment = @$mh_board_options['mh_comment'];
$short_link = get_site_url()."/?p=".$post->ID;
unset($mh_board_options);
if($mh_comment){
	require_once(dirname(__FILE__).'/mhboard-comment.php');	
}else{
	comments_template('',true);
}
?>
</div>