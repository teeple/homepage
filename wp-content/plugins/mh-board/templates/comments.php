<?php
if(function_exists('mh_socialnuri_form')):
	mh_socialnuri_form();
else:
if(isset($_POST['comment']) && isset($_POST['comment_post_ID']) && $_POST['comment_post_ID'] == get_the_ID()){
	$time = current_time('mysql');

	
	if(!is_user_logged_in()){
		$comment_author = $_POST['author'];
		$comment_author_email = $_POST['email'];
		$comment_author_url = $_POST['url'];
	}else{
		$user_id = get_current_user_id();
		$user_data = get_userdata($user_id);
		$comment_author = $user_data->display_name;
		$comment_author_email = $user_data->user_email;
		$comment_author_url = $user_data->user_url;
	}
	$data = array(
		'comment_post_ID' => get_the_ID(),
		'comment_author' => $comment_author,
		'comment_author_email' => $comment_author_email,
		'comment_author_url' => $comment_author_url,
		'comment_content' => htmlspecialchars($_POST['comment']),
		'comment_type' => '',
		'comment_parent' => 0,
		'user_id' => $user_id,
		'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
		'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
		'comment_date' => $time,
		'comment_approved' => 1,
	);
	wp_insert_comment($data);
}
global $post;
?>
<div class="mh-comment">
	<h3>댓글을 남겨주세요.</h3>
	<?php if(current_user_can('administrator') && isset($post->post_password)):?>
	<form action="" method="post">
	<?php else :?>
	<form action="<?php echo home_url('/wp-comments-post.php');?>" method="post">
	<?php endif;?>
		<div class="mh-comment-item">
			<div class="comment-content">
				<?php if(!get_current_user_id()):?>
				<?php
				$commenter = wp_get_current_commenter();
				?>
				<p class="comment-form-author"><label for="author">Name</label> <span class="required">*</span><input id="author" name="author" type="text" value="<?php echo esc_attr( $commenter['comment_author'] );?>" size="30" aria-required="true"></p>
				<p class="comment-form-email"><label for="email">Email</label> <span class="required">*</span><input id="email" name="email" type="text" value="<?php echo esc_attr(  $commenter['comment_author_email'] );?>" size="30" aria-required="true"></p>
				<p class="comment-form-url"><label for="url">Website</label><input id="url" name="url" type="text" value="<?php echo esc_attr( $commenter['comment_author_url'] );?>" size="30"></p>
				<?php endif;?>
				<div class="header">
					<?php if(get_current_user_id()):?>
					<?php echo get_avatar( get_current_user_id(), 55 ); ?>
					<?php else:?>
					<?php endif;?>
				</div>
				<p><textarea id="comment" name="comment" aria-required="true"></textarea></p>
				<p class="form-submit clearfix">
					<input name="submit" type="submit" id="submit" value="댓글 등록" class="button btn">
					<input type="hidden" name="comment_post_ID" value="<?php the_ID();?>" id="comment_post_ID">
					<input type="hidden" name="comment_parent" id="comment_parent" value="0">
				</p>
			</div>
		</div>
	</form>
	<h3>댓글 : <?php echo get_comments_number();?></h3>
		<?php $comments = get_comments( array( 'post_id' => get_the_ID(), 'order' => 'ASC' ));?>
		<?php foreach($comments as $comment):?>
			<div class="mh-comment-item">
				<div class="comment-content">
					<div class="header">
						<small><?php echo date('Y.m.d G:i:s',strtotime($comment->comment_date));?></small>
						<?php echo get_avatar( $comment, 55 ); ?>
						<strong><?php echo $comment->comment_author;?> </strong>
						<span><?php echo comment_reply_link(array('',$comment->comment_ID,get_the_ID()));?></span>
					</div>
					<p><?php echo nl2br($comment->comment_content);?></p>
				</div>
			</div>
		<?php endforeach;?>
</div>
<?php endif;?>