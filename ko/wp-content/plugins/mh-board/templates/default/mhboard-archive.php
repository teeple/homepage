<?php
global $mh_board, $mh_board_options,$board_cat;
$s = empty($_GET['s']) ? '' : $_GET['s'];
?>
<div id="mh-board" class="content mh-board-list" class="clearfix">
	<?php mhb_get_template_part( 'mhboard','category-menu');?>
	<div class="board-list">
	<form action="" method="get" class="board-search-frm">
		<fieldset class="board-search">
			<input type="text" name="s" value="<?php echo $s;?>"/>
			<input type="submit" value="검색" class="button"/>
		</fieldset>
	</form>
	<table cellpadding="0" cellspacing="0" class="board">
	<thead>
		<tr class="mh_b_header">		
			<th class="mh_b_no" width="30px"><?php echo __('No' ,'mhboard');?></th>
			<?php if(@$mh_board_options['mh_category'] != 1):?>
				<th class="mh_b_category"><?php echo __('Category' ,'mhboard');?></th>
			<?php endif;?>
			<th class="mh_b_title" width="40%"><?php echo __('Title' ,'mhboard');?></th><th class="mh_b_author"><?php echo __('Author' ,'mhboard');?></th><th class="mh_b_date"><?php echo __('Date' ,'mhboard');?></th><th class="mh_b_count" width="70px"><?php echo __('Count' ,'mhboard');?></th>		
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
	if(!@$board_cat){
		$total = " class='current current-menu-item selected'";
	}
	?>
	<?php
	$afterdate = strtotime('+2 day',strtotime(get_the_date('Y/m/d')));
	$notime = time();
	$new = '';
	if($notime <= $afterdate){
		$new = " <img src=\"".plugins_url('images/new.gif',dirname(__FILE__))."\" alt=\"new\" align=\"absmiddle\"/>";
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
					<td class="mh_b_no"><?php echo __('Notice' ,'mhboard');?></td>
				<?php if(@$mh_board_options['mh_category'] != 1):?>	
					<td class="mh_b_category"><?php echo $category[0]->name;?></td>
				<?php endif;?>
					<td class="mh_b_title">
						<a href="<?php the_permalink();?>"><?php the_title(); ?>
						<?php if(get_comments_number() > 0){
							echo  "[".get_comments_number()."]";
						}?>
						</a><?php echo $new;?>
					</td>
					<td class="mh_b_author"><?php echo $author;?></td><td class="mh_b_date"><?php echo get_the_date('Y/m/d');?></td>
					<td class="mh_b_count"><?php echo $mh_board->get_count(get_the_ID());?></td>
				</tr>
		<?php endwhile; ?>
	<?php endif;?>
	<?php
	global $paged;
	$posts_per_page = empty($mh_board_options['mh_posts_per_page']) ? '10' : $mh_board_options['mh_posts_per_page'];
	$paged = ($paged <=0) ? 1 : $paged;
	$args= array (
		'post_type' => array('board'),
		'post_status' => array('publish'),
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
	if(!@$board_cat){
		$total = " class='current current-menu-item selected'";
	}
	?>
	<?php if ( $mh_query->have_posts() ) : 
		
		$no = $mh_query->found_posts -($posts_per_page * ($paged - 1));?>
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
					$new = " <img src=\"".plugins_url('images/new.gif',dirname(__FILE__))."\" alt=\"new\" align=\"absmiddle\"/>";
				}
				$file = "";
				$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post->ID );
				$attachments = get_posts($args);
				if($attachments){
					$file = " <img src=\"".plugins_url('images/file.gif',dirname(__FILE__))."\" alt=\"file\" align=\"absmiddle\"/>";
				}
				unset($attachments);
			?>
				<tr>
					<td class="mh_b_no"><?php echo $no;?></td>
					<?php if(@$mh_board_options['mh_category'] != 1):?>	
					<td class="mh_b_category"><?php echo $category[0]->name;?></td>
					<?php endif;?>
					<td class="mh_b_title">
						<a href="<?php the_permalink();?>"><?php the_title(); ?>
						<?php if(get_comments_number() > 0){
							echo  "[".get_comments_number()."]";
						}?>
						</a><?php echo $new.$file;?>
					</td>
					<td class="mh_b_author"><?php echo $author;?></td><td class="mh_b_date"><?php echo get_the_date('Y/m/d');?></td>
					<td class="mh_b_count"><?php echo $mh_board->get_count(get_the_ID());?></td>
				</tr>
				<?php
				$args= array (
					'post_type' => array('board'),
					'post_status' => array('publish','private'),
					'posts_per_page'=>10,
					'orderby' =>'post_date',
					'order' => 'ASC',
					'board_cat'=> @$board_cat,
					'post_parent' => get_the_ID()
			
				);
				$no--;
				$query = new WP_Query($args);
				if(!@$board_cat){
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
							<td class="mh_b_no"></td>
						<?php if(@$mh_board_options['mh_category'] != 1):?>	
							<td class="mh_b_category"></td>
						<?php endif;?>
							
							<td class="mh_b_title"><a href="<?php the_permalink();?>">└ Re:<?php the_title(); ?>[<?php echo  get_comments_number();?>]</a></td><td class="mh_b_author"><?php echo $author;?></td><td class="mh_b_date"><?php echo get_the_date('Y/m/d');?></td><td class="mh_b_count"><?php echo $mh_board->get_count(get_the_ID());?></td>
						</tr>
						<?php endwhile; ?>
					<?php  endif;?>
		<?php endwhile; ?>
	<?php endif;?>
	</table>
	<div class="copyright">
		<a href="http://ssamture.net"><img src="http://ssamture.net/ssamturenet.png" border="0"/></a>
	</div>
	<?php
	$guestwrite = $mh_board_options['mh_guestwrite'];
	$para = '';
	if($board_cat){
		$para = '&board_cat='.$board_cat;
	}
	$redirect_url = '&redirect_url='.urlencode(@$_SERVER['REDIRECT_URL']);
	?>
	<?php if($guestwrite == '1' || is_user_logged_in()):?>
		<a href="<?php echo wp_nonce_url(mh_get_board_write_link(),'_mh_board_nonce').$redirect_url.$para;?>" class="button"><?php echo __('Write' ,'mhboard');?></a>
	<?php endif;?>
	<div class="pagenavi">
	<?php
	mh_pagenavi();
	?>
	</div>
	</div>
</div>