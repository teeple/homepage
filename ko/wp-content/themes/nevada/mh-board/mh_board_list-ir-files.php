<?php
/**
 * 숏코드용 게시판 리스트 템플릿
 */
global $board_cat;
$s = empty($_GET['s']) ? '' : $_GET['s'];
$file = '';
?>
<div id="mh-board" class="content" class="clearfix">
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
	<table cellpadding="0" cellspacing="0" class="board">
	<thead>
		<tr class="mh_b_header">		
			<th class="mh_b_no" width="30px"><?php echo __('No' ,'mhboard');?></th>
			<?php /* if(@$mh_board_options['mh_category'] != 1):?>
				<th class="mh_b_category"><?php echo __('Category' ,'mhboard');?></th>
			<?php endif; */?>
			<th class="mh_b_title" width="40%"><?php echo __('Title' ,'mhboard');?></th><?php /* 082 START ?><th class="mh_b_author"><?php echo __('Author' ,'mhboard');?></th><?php */// 082 END ?><th class="mh_b_date"><?php echo __('Date' ,'mhboard');?></th><?php /* 082 START ?><th class="mh_b_count" width="70px"><?php echo __('Count' ,'mhboard');?></th><?php */// 082 END ?>		
			<th class="mh_b_title">다운로드</th>
		</tr>
	</thead>
	<?php
	$redirect_uri = @$_SERVER['REDIRECT_URL'];
	$args= array (
		'post_type' => array('board'),
		'post_status' => array('publish','private'),
		'posts_per_page'=>5,
		'paged'=>1,
		'orderby' =>'post_date',
		'order' => 'DESC',
		'board_cat'=>	@$board_cat,
		'meta_key'=>'mh_board_notice',
		'meta_value'=>'1',

	);
	$mh_query = new WP_Query($args);
	if(!$board_cat){
		$total = " class='current current-menu-item selected'";
	}
	?>
	<?php
	$afterdate = strtotime('+2 day',strtotime(get_the_date('Y/m/d')));
	$notime = time();
	$new = '';
	if($notime <= $afterdate){
		$new = " <img src=\"".plugins_url('templates/images/new.png',dirname(dirname(__FILE__)))."\" alt=\"new\" align=\"absmiddle\"/>";
	}
	?>
	<?php if ( $mh_query->have_posts() ) : ?>
		<?php while ( $mh_query->have_posts() ) : $mh_query->the_post(); ?>
			<?php $category = wp_get_object_terms(get_the_ID(),'board_cat');?>
			<?php
				$author = get_the_author();
				if($author){
					
				}else{
					$author = get_post_meta(get_the_ID(),'guest_info',true);
					$author = $author['guest_name'];
				}
			?>
				<tr>
					<td class="mh_b_no"><?php echo __('Notice' ,'mhboard');?></td><?php if(@$mh_board_options['mh_category'] != 1):?>	
					<td class="mh_b_category"><?php echo $category[0]->name;?></td>
				<?php endif;?>
				<td class="mh_b_title">
					<a href="<?php mh_board_view_link(get_the_ID());?>"><?php the_title(); ?>
					<?php if(get_comments_number() > 0){
						echo  "[".get_comments_number()."]";
					}?>
					</a><?php echo $new.$file;?>
				</td>
				<td class="mh_b_author"><?php echo $author;?></td><td class="mh_b_date"><?php echo get_the_date('Y/m/d');?></td><td class="mh_b_count"><?php echo $mh_board->get_count(get_the_ID());?></td>
				<td>
				<?php
				$attachments_args = array( 'post_type' => 'attachment', 'numberposts' => 1, 'post_status' => null, 'post_parent' => $post->ID, 'fields' => 'ids' );
				$attachments = get_posts($attachments_args);
				var_dump($attachments);
				?>
				</td>
				</tr>
		<?php endwhile; ?>
	<?php endif;?>
	<?php
	$redirect_uri = @$_SERVER['REDIRECT_URL'];
	$posts_per_page = empty($mh_board_options['mh_posts_per_page']) ? '10' : $mh_board_options['mh_posts_per_page'];
	$args= array (
		'post_type' => array('board'),
		'post_status' => array('publish','private'),
		'paged'=>$paged,
		'orderby' =>'post_date',
		'order' => 'DESC',
		'board_cat'=>	@$board_cat,
		'post_parent' => 0,
		'posts_per_page'=> $posts_per_page
		//'meta_key'=>'mh_board_notice',
		//'meta_value'=>'0',

	);
	if($s){
		$args['s'] = $s;
	}
	global $mh_query;
	$mh_query = new WP_Query($args);
	if(!$board_cat){
		$total = " class='current current-menu-item selected'";
	}
	?>
	<?php if ( $mh_query->have_posts() ) : ?>
		<?php while ( $mh_query->have_posts() ) : $mh_query->the_post(); ?>
			<?php $category = wp_get_object_terms(get_the_ID(),'board_cat');?>
			<?php
				$author = get_the_author();
				if($author){
					
				}else{
					$author = get_post_meta(get_the_ID(),'guest_info',true);
					$author = $author['guest_name'];
				}
			?>
			<?php
				$afterdate = strtotime('+2 day',strtotime(get_the_date()));
				$notime = time();
				$new = '';
				if($notime <= $afterdate){
					$new = " <img src=\"".plugins_url('templates/images/new.png',dirname(dirname(__FILE__)))."\" alt=\"new\" align=\"absmiddle\"/>";
				}
			?>
				<tr>
					<td class="mh_b_no"><?php the_ID();?></td>
					<?php /* if(@$mh_board_options['mh_category'] != 1):?>	
					<td class="mh_b_category"><?php echo $category[0]->name;?></td>
					<?php endif; */?>
					<td class="mh_b_title">
						<a href="<?php mh_board_view_link(get_the_ID());?>"><?php the_title(); ?>
						<?php if(get_comments_number() > 0){
							echo  "[".get_comments_number()."]";
						}?>
						</a><?php echo $new.$file;?>
					</td><?php /* 082 START ?><td class="mh_b_author"><?php echo $author;?></td><?php */// 082 END ?><td class="mh_b_date"><?php echo get_the_date('Y/m/d');?></td><?php /* 082 START ?><td class="mh_b_count"><?php echo $mh_board->get_count(get_the_ID());?></td><?php */// 082 END ?>
				<?php /* 082 START */ ?><td class="mb_b_title">
				<?php
				$attachment_args = array( 'post_type' => 'attachment', 'numberposts' => 1, 'post_status' => null, 'post_parent' => $post->ID, 'fields' => 'ids', 'orderby' => 'ID,menu_order', 'order' => 'ASC' );
				if ( $attachment = get_posts($attachment_args) ) {
					if ( is_callable('WP_DMGR', '_content') ) {
						$dl_url = add_query_arg(array('dl' => $attachment[0]), home_url());
						$file_url = add_query_arg(array('dl' => $attachment[0], 'directdl'=>'true'), home_url());
					} else {
						$dl_url = $file_url = wp_get_attachment_url($attachment[0]);
					}
					echo '<a href="'.$file_url.'" onclick="return uangel_popup(this, \'View attachment\', 700, 700);" target="_blank">Direct View</a> | <a href="'.$dl_url.'" onclick="window.open(this.href); return false;" target="_blank">Download</a>';
				}
				?>
				</td><?php /* 082 END */ ?>
				</tr>
				<?php
				$args= array (
					'post_type' => array('board'),
					'post_status' => array('publish','private'),
					'posts_per_page'=>10,
					'orderby' =>'post_date',
					'order' => 'ASC',
					'board_cat'=>	@$board_cat,
					'post_parent' => get_the_ID()
			
				);
				$query = new WP_Query($args);
				if(!$board_cat){
					$total = " class='current current-menu-item selected'";
				}
				?>
				<?php if ( $query->have_posts() ) : ?>
					<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						<?php $category = wp_get_object_terms(get_the_ID(),'board_cat');?>
						<?php
							$author = get_the_author();
							if($author){
								
							}else{
								$author = get_post_meta(get_the_ID(),'guest_info',true);
								$author = $author['guest_name'];
							}
						?>
						<tr>
							<td class="mh_b_no"></td><?php if(@$mh_board_options['mh_category'] != 1):?>	
							<td class="mh_b_category"></td>
						<?php endif;?>
						<td class="mh_b_title"><a href="<?php mh_board_view_link(get_the_ID());?>">└ Re:<?php the_title(); ?>[<?php echo  get_comments_number();?>]</a></td><td class="mh_b_author"><?php echo $author;?></td><td class="mh_b_date"><?php echo get_the_date('Y/m/d');?></td><td class="mh_b_count"><?php echo $mh_board->get_count(get_the_ID());?></td>
						</tr>
						<?php endwhile; ?>
					<?php endif;?>
		<?php endwhile; ?>
	<?php endif;?>
	</table>
	<div class="copyright">
		<a href="http://ssamture.net"><img src="http://ssamture.net/ssamturenet.png" border="0"/></a>
	</div>
	<?php
	$guestwrite = $mh_board_options['mh_guestwrite'];
	$redirect_to = @$_SERVER['REQUEST_URI'];
	?>
	<?php if($guestwrite == '1' || is_user_logged_in()):?>
		<a href="<?php echo wp_nonce_url(get_mh_board_write_link(),'_mh_board_nonce');?>&redirect_to=<?php echo urlencode(site_url($redirect_to));?>" class="button">글쓰기</a>
	<?php endif;?>
	<div class="pagenavi">
	<?php
	mh_pagenavi();
	?>
	</div>
</div>