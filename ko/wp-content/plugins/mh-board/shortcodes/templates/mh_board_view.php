<?php
/**
 * 숏코드용 게시판 리스트 템플릿
 */
do_action('mh_board_read_permission');
if(!$mh_board_options['permission']){
	echo __('Access Denied','mhboard');
	return false;
}
$wp_post_ID = get_the_ID();
?>

<?php $mh_board_options = get_option('mh_board_options');?>
	<div id="mh-board">
	<?php $mh_board_options = get_option('mh_board_options');
	if(@$mh_board_options['mh_category'] != 1):?>
	<div id="menu" class="clearfix">
		<ul>
			<li><a href="<?php echo $mh_board_link;?>">전체</a></li>
			<?php
			$categories = @ get_terms('board_cat',array('orderby'=>'id','order'=>'ASC','hide_empty'=>0));
			if(is_array($categories)){
				foreach($categories as $category){
					echo '<li><a href="'.$mh_board_link.'board_cat='.$category->slug.'">'.$category->name.'</a></li>';
				}
			}
			?>			
		</ul>
	</div>
	<?php endif;?>
	<table cellpadding="0" cellspacing="0" class="board view">
	<?php
	$args= array (
		'p'=>$ID,
		'post_type' => array('board'),
		'post_status' => array('publish','private'),
		'posts_per_page'=>5,
		'paged'=>1,
		'orderby' =>'post_date',
		'order' => 'DESC',
		'board_cat'=>	$board_cat,

	);
	$mh_query = new WP_Query($args);
	?>
	<?php if ( $mh_query->have_posts() ) : ?>
		<?php while ( $mh_query->have_posts() ) : $mh_query->the_post(); ?>
			<?php $category =@ wp_get_object_terms(get_the_ID(),'board_cat');?>
			<?php
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
			<!-- <tr>
				<th><?php //echo __('Tags' ,'mhboard');?></th>
				<td colspan="5"><?php //the_tags('',',','');?></td>
			</tr> 082 -->
		<?php /* 082 START */
		global $wp_query;
		$___backup_id = $wp_query->post->ID;
		$wp_query->post->ID = $post->ID;
		$mh_board->wpp_print_ajax();
		$wp_query->post->ID = $___backup_id;
		/* 082 END */?>
		<?php endwhile; ?>
	<?php endif;
	?>
		
	</table>
	<div class="copyright">
		<a href="http://ssamture.net"><img src="http://ssamture.net/ssamturenet.png" border="0"/></a>
	</div>
	<div class="action clearfix" style="margin-top:15px;">
		<?php if(is_admin()):?>
			<a href="<?php echo mh_get_board_write_link();?>" class="button">글쓰기</a>
		<?php endif;?>
		<?php if(is_user_logged_in() && get_current_user_id() == get_the_author_meta('ID')):?>
			<form action="<?php echo mh_board_edit_link(get_the_ID());?>" method="post"><input type="hidden" name="post_id" value="<?php the_ID();?>"/>
				<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
				<input type="submit" class="button" value="수정"/>
			</form>
			<form action="<?php echo mh_board_edit_link(get_the_ID());?>" method="post" id="delete_board"><input type="hidden" name="post_id" value="<?php the_ID();?>"/>
				<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
				<input type="hidden" name="action" value="delete"/>
				<input type="submit" class="button" value="삭제"/>
			</form>
		<?php elseif($action == 'guest'):?>
			<form action="<?php echo mh_board_edit_link(get_the_ID());?>" method="post"><input type="hidden" name="post_id" value="<?php the_ID();?>"/>
				<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
				<input type="submit" class="button" value="수정"/>
			</form>
			<form action="<?php echo mh_board_edit_link(get_the_ID());?>" method="post" id="delete_board"><input type="hidden" name="post_id" value="<?php the_ID();?>"/>
				<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
				<input type="hidden" name="action" value="delete"/>
				<input type="hidden" name="edit_type" value="guest"/>
				<input type="submit" class="button" value="삭제"/>
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
		<?php if($post->post_parent == 0 && @$mh_board_options['mh_replypost'] == 1):
		$redirect_to = @$_SERVER['REQUEST_URI'];
		?>
			<form action="<?php echo wp_nonce_url(get_mh_board_write_link(),'_mh_board_nonce');?>&redirect_to=<?php echo urlencode(site_url(@$redirect_to));?>&post_id=<?php the_ID();?>" method="post">
				<?php if(function_exists('wp_nonce_field'))	wp_nonce_field('mh_board_nonce','_mh_board_nonce');?>
				<input type="submit" class="button" value="답글"/>
			</form>
		<?php endif;?>
		<form action="<?php echo get_page_link($wp_post_ID) ;?>" method="post">
			<input type="submit" class="button" value="LIST"/>
		</form>
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
		require_once(dirname(dirname(dirname(__FILE__))).'/templates/comments.php');	
	}else{
		comments_template('',true);
	}
	?>
	</div>