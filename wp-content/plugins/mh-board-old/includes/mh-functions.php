<?php
function mh_media_buttons() {
	// If we're using http and the admin is forced to https, bail.
	if ( ! is_ssl() && ( force_ssl_admin() || get_user_option( 'use_ssl' ) )  ) {
		return;
	}

	include_once( ABSPATH . '/wp-admin/includes/media.php' );
	ob_start();
	do_action( 'media_buttons' );

	// Replace any relative paths to media-upload.php
	echo preg_replace( '/([\'"])media-upload.php/', '${1}' . admin_url( 'media-upload.php' ), ob_get_clean() );
}
function mh_pagenavi( $args = array() ){
	global $mh_query;
	$args['items'] = 5;
	$max_page_num = @$mh_query->max_num_pages;
	$current_page_num = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	$befores = $current_page_num - floor( ( $args['items'] - 1 ) / 2 );
	$afters = $current_page_num + ceil( ( $args['items'] - 1 ) / 2 );

	if ( $max_page_num <= $args['items'] ) {
		$start = 1;
		$end = $max_page_num;
	} elseif ( $befores <= 1 ) {
		$start = 1;
		$end = $args['items'];
	} elseif ( $afters >= $max_page_num ) {
		$start = $max_page_num - $args['items'] + 1;
		$end = $max_page_num;
	} else {
		$start = $befores;
		$end = $afters;
	}
	if($start >= 2){
		$previous_num = max( 1, $start - 1 );
?>
		<a href="<?php echo get_pagenum_link();?>" class="pre"><<</a>
		<a href="<?php echo get_pagenum_link( $previous_num );?>" class="pre"><</a>
<?php		
	}
	for ( $i = $start; $i <= $end; $i++ ) {
		if ( $i == $current_page_num ) {
			echo "<strong>{$i}</strong>";
		}else{
			?><a href="<?php echo get_pagenum_link($i);?>"><?php echo $i;?></a><?php
		}
	}	
	if($current_page_num != $max_page_num  && $max_page_num > $args['items']){
		$next_num = min( $max_page_num, $end + 1 );
?>
		<a href="<?php echo get_pagenum_link($next_num);?>" class="next">></a>
        <a href="<?php echo get_pagenum_link( $max_page_num );?>" class="next">>></a>
<?php		
	}
?>
<?php
}
function mh_board_register_default_page(){
	$mh_board_default_pagel = array(
		'Write'=>'[mh_board_write_form]',
		'Edit'=>'[mh_board_edit_form]',
	);
	foreach($mh_board_default_pagel as $post_title => $post_content){
		$args = array(
			'post_title' =>$post_title, 
			'post_status' => 'publish', 
			'post_type' => 'page',
			'post_author' => 1,
			'ping_status' => get_option('default_ping_status'),
			'comment_status' => 'closed',
			'post_content' => $post_content
		);

		if(!get_page_by_title($post_title)){
		  wp_insert_post( $args );
		}
	}
}
add_action( 'init', 'mh_board_register_default_page', 0 );
require_once(dirname(__FILE__).'/mh-actions.php');
require_once(dirname(dirname(__FILE__)).'/shortcodes/write_form.php');
require_once(dirname(dirname(__FILE__)).'/shortcodes/edit_form.php');
require_once(dirname(dirname(__FILE__)).'/shortcodes/mh_board.php');
add_shortcode('mh_board_write_form','mh_board_write');
add_shortcode('mh_board_edit_form','mh_board_edit');
add_shortcode('mh_board','mh_board');

//add_filter('pre_option_posts_per_page', 'mh_limit_posts_per_page');
function mh_limit_posts_per_page(){
	global $wp_query;
	if ( @$wp_query->query_vars['post_type'][0]=='board'){
        return 10;
    }else{
    	$all_options = wp_load_alloptions();
        return $all_options["posts_per_page"]; // default: 5 posts per page
    }
}
function my_custom_posts_per_page( &$q ) {
    if ( $q->is_archive ) // any archive
    if(@$q->query_vars['post_type'] == 'board'){  //custom post type "faq" archive
    $q->set( 'posts_per_page', 5 );
    }
   	return $q;
}

//add_filter('parse_query', 'my_custom_posts_per_page');
function mh_get_board_write_link($post_id = '',$par = ''){
	global $wpdb;
	$board_link =  get_post_type_archive_link('board');
	$write_link = '';
	if(strstr($board_link, '?')){
		$write_link .= $board_link . '&write=1';
	}else{
		$write_link .= $board_link . '?write=1';
		
	}
	if($post_id > 0){
		$write_link .= '&board_id='.$post_id;
	}
	return $write_link;

	if($link = get_option('mh_board_write_link')){
		return $link;
	}else if($result = $wpdb->get_results("select ID,guid from {$wpdb->prefix}posts where post_type = 'page' and post_content like '%[mh_board_write_form]%' and post_status = 'publish'")){
		if(get_option('permalink_structure') == ''){
			update_option('mh_board_write_link',home_url('?page_id='.$result[0]->ID));
			return home_url('?page_id='.$result[0]->ID);
		}else{
			update_option('mh_board_write_link',$result[0]->guid);
			return $result[0]->guid;	
		}
		
	}else{
		mh_board_register_default_page();
		return '/write';
	}
}
function mh_get_board_edit_link(){
	global $wpdb,$post;
	$board_link =  get_permalink();
	$category = wp_get_object_terms(get_the_ID(),'board_cat');
	$link = '';
	if(strstr($board_link, '?')){
		$link = $board_link . '&edit=1';	
	}else{
		$link = $board_link . '?edit=1';
	}
	
	if($category[0]->slug){
		$link .= '&board_cat='.$category[0]->slug;
	}
	return $link;
	if($link = get_option('mh_board_edit_link')){
		return $link;
	}else if($result = $wpdb->get_results("select ID,guid from {$wpdb->prefix}posts where post_type = 'page' and post_content like '%[mh_board_edit_form]%' and post_status = 'publish'")){
		if(get_option('permalink_structure') == ''){
			update_option('mh_board_edit_link',home_url('?page_id='.$result[0]->ID));
			return home_url('?page_id='.$result[0]->ID);	
		}else{
			update_option('mh_board_edit_link',$result[0]->guid);
			return $result[0]->guid;	
		}
	}else{
		mh_board_register_default_page();
		return '/edit';
	}
}
function mh_get_board_link_by_board_cat($board_cat = ''){
	if($board_cat){
		return get_term_link(intval($board_cat),'board_cat');
	}else{
		return get_post_type_archive_link('board');
	}

}
function mh_update_post_author($post_id , $author = 0){
	global $wpdb;
	if($wpdb->query("update {$wpdb->prefix}posts set post_author = $author where ID = $post_id")){
		return true;
	}
	return false;
}
add_action('admin_notices','mh_board_notice');
function mh_board_notice(){
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
		$xml = @wp_remote_get(MH_BOARD_UPDATE_URL);
		$data = @simplexml_load_string($xml['body']);
	}
	if(is_object($data)){
	$is_update = false;
	if($data->version != MH_BOARD_VERSION){
		$is_update = true;
	}
	if($data->notice_status == 1){
		echo "<div class=\"updated\"><p>{$data->notice_content}</p></div>";
	}
	if (! current_user_can('manage_options') || $is_update == false ) 
        return;
	echo "<div class=\"updated\"><p>현재 설치되어 있는 워드프레스 게시판 \"MH Board\"의 버전은 ".MH_BOARD_VERSION."이며 버전 {$data->version} 가 새로 배포되었습니다.</p>";
	echo "<p>다운로드 받으러 가기: <a href='{$data->download}'>$data->download</a></p></div>";
	}

}
//short code board 
function mh_board_view_link($post_id){
	echo get_mh_board_view_link($post_id);
}
function get_mh_board_view_link($post_id){
	global $mh_board_link;
	if($mh_board_link)
		$board_link = $mh_board_link;
	else
		$board_link = get_pagenum_link();
	
	if(strstr($board_link,'?')){
		$board_link .= '&type=view&ID='.$post_id;
	}else{
		$board_link .= '?type=view&ID='.$post_id;
	}
	return $board_link;
}
function mh_board_edit_link($post_id, $board_cat = ''){
	global $mh_board_link;
	if($mh_board_link)
		$board_link = $mh_board_link;
	else
		$board_link = get_pagenum_link();
	
	if(strstr($board_link,'?')){
		$board_link .= '&type=edit&ID='.$post_id;
	}else{
		$board_link .= '?type=edit&ID='.$post_id;
	}
	echo $board_link;
}
function mh_board_write_link(){
	echo get_mh_board_write_link();
}
function get_mh_board_write_link(){
	global $mh_board_link;
	if($mh_board_link)
		$board_link = $mh_board_link;
	else
		$board_link = get_pagenum_link();
	
	if(strstr($board_link,'?')){
		$board_link .= '&type=write';
	}else{
		$board_link .= '?type=write';
	}
	return $board_link;
}
function mh_board_list_link($args = array()){
	$board_link = $_SERVER['HTTP_REFERER'];
	if(strstr($board_link,'?')){
		foreach($args as $key => $value){
			$board_link .= '&'.$key.'='.$value;
		}
	}else{
		$board_link .= '?';
		foreach($args as $key => $value){
			$board_link .= $key.'='.$value.'&';
		}
	}
	return $board_link;
}
add_filter('wp_handle_upload_prefilter','mh_board_replace_filename');
function mh_board_replace_filename($file){

	$file['name'] = str_replace('php','php.txt',$file['name']);

	return $file;
}
//파일 업로드 관련
function mh_board_insert_attachment($file_handler,$post_id,$setthumb='false') {
	// check to make sure its a successful upload
	if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	require_once(ABSPATH . "wp-admin" . '/includes/media.php');

	$attach_id = media_handle_upload( $file_handler, $post_id );
	update_post_meta($post_id,'attach_'.$attch_id,$_FILES[$file_handler]['name']);
	if ($setthumb) update_post_meta($post_id,'_thumbnail_id',$attach_id);
	return $attach_id;
}
//get recent board
function get_recent_mh_board($board_cat = '', $posts_per_page = 5){
	$category = get_term_by('name',$board_cat,'board_cat');
	if(!$category){
		$category = get_term_by('slug',$board_cat,'board_cat');
	}
	
	$args= array (
		'post_type' => array('board'),
		'post_status' => array('publish'),
		'posts_per_page'=>$posts_per_page,
		'paged'=>1,
		'orderby' =>'post_date',
		'order' => 'DESC',
		'board_cat'=> $category->slug,

	);
	
	$mh_query = new WP_Query($args);?>
	<h4><?php echo $category->name;?></h4>
	<a href="<?php echo get_term_link($category->slug,'board_cat');?>" class="com_more">+더보기</a>
	<?php if ( $mh_query->have_posts() ) : ?>
	
	<ul>
	<?php while ( $mh_query->have_posts() ) : $mh_query->the_post(); ?>
	<?php
	$afterdate = strtotime('+2 day',strtotime(get_the_date('Y/m/d')));
	$notime = time();
	$new = '';
	if($notime <= $afterdate){
		$new = " <img src=\"".plugins_url('/templates/images/new.gif',dirname(__FILE__))."\" alt=\"new\" align=\"absmiddle\"/>";
	}
	?>
		<li><a href="<?php the_permalink();?>"><?php the_title(); ?>
						<?php if(get_comments_number() > 0){
							echo  "[".get_comments_number()."]";
						}?>
						</a><?php echo $new;?></li>
	<?php endwhile; ?>
	</ul>
		
		
	<?php endif;?><?php
}
function mh_get_user_role(){
	global $user_role;
	if($user_role){
		return $user_role;
	}
	if($user_role = get_userdata(get_current_user_id())){
		$user_role = $user_role->roles[0];
	}else{
		$user_role = 'guest';
	}
	return $user_role; 
}
add_action('mh_board_read_permission','mh_board_read_permission');
function mh_board_read_permission(){
	global $post,$mh_board_options;
	if(isset($_GET['ID'])){
		$post_id = $_GET['ID'];
	}else{
		$post_id = $post->ID;
	}
	$category =@ wp_get_object_terms($post_id,'board_cat');

	$mh_board_per_o = get_option('mh_board_permission_'.$category[0]->term_id);
	$mh_board_options['permission']  = true;
	if($mh_board_per_o[mh_get_user_role()]['read'] == 'off'){
		$mh_board_options['permission']  = false;
	}
}
add_action('mh_board_write_permission','mh_board_write_permission');
function mh_board_write_permission(){
	global $post,$mh_board_options,$board_cat;
	
	if(isset($_GET['board_cat'])){
		$board_cat = $_GET['board_cat'];
	}
	if(isset($_GET['board_cat']) || isset($board_cat)){
		
		$category =@ get_term_by('slug',$board_cat,'board_cat');

		$mh_board_per_o = get_option('mh_board_permission_'.$category->term_id);
		$mh_board_options['permission'] = true;
		if($mh_board_per_o[mh_get_user_role()]['write'] == 'off'){
			$mh_board_options['permission'] = false;
		}
	}
}
add_action('mh_screens','mh_board_action');
function mh_board_action(){
	global $mh_error;
	$mh_error = new stdClass;
	if(empty($_REQUEST['mh_action'])){
		return '';
	}
	if(@$_REQUEST['mh_action'] == 'post' && wp_verify_nonce($_REQUEST['_mh_board_nonce'],'mh_board_nonce')){ //글쓰기 관련
		$mh_board_write = new MH_Register_Post();
		$user_id        = get_current_user_id() ? get_current_user_id() : 0;
		$tags           = trim( $_POST['board_tag'] );
		$post_title		= $_POST['board_title'];
		$post_content	= $_POST['board_content'];
		$mh_board_write->post_data = array(
			'post_author'   => $user_id,
			'post_title'    => $post_title,
			'post_content'  => $post_content,
			'post_type'     => 'board',
			'tags_input'    => $tags,
			'post_status'   => 'publish',
			'comment_status' => 'open',
		);
		if(isset($_POST['board_parent']) && $_POST['board_parent'] > 0){
			$mh_board_write->post_data['post_parent'] = $_POST['board_parent'];
		}

		if(isset($_POST['board_tag'])){
			$mh_board_write->post_data['tags_input'] = $_POST['board_tag'];
		}
		if($_POST['board_open'] == 0 && $_POST['board_password']){
			$mh_board_write->post_data['post_password'] = $_POST['board_password'];
		}
		if($user_id == 0 && $_POST['guest_name'] && $_POST['guest_email'] && $_POST['guest_password']){
			$guest_info = array(
				'guest_name' => $_POST['guest_name'],
				'guest_email' => $_POST['guest_email'],
				'guest_password' => $_POST['guest_password'],
				'guest_site' => $_POST['guest_site']
			);
			$mh_board_write->post_meta = array(
				'guest_info' => $guest_info
			);
		}
		
		$mh_board_write->post_term = array(
			'terms' => array(intval($_POST['board_category'])),
			'taxonomy' => 'board_cat'
		);
		$mh_board_write->post_meta['mh_board_notice'] = (int)0;
		$term = get_term_by('id',$_POST['board_category'],'board_cat');
		if($pid = $mh_board_write->register_post()){
			if($_FILES){
				foreach($_FILES as $file => $array){
					$newupload = mh_board_insert_attachment($file,$pid);
				}
			}
			wp_redirect(mh_get_board_link_by_board_cat($_POST['board_category']));
		}else{
			
		}
	}else if(@$_REQUEST['mh_action'] == 'update' && wp_verify_nonce($_REQUEST['_mh_board_nonce'],'mh_board_nonce')){
		print_r($_POST);
		$mh_board_update = new MH_Update_Post();
		$user_id        = get_current_user_id() ? get_current_user_id() : 0;
		$tags           = trim( $_POST['post_tag'] );
		$post_title		= empty($_POST['post_title']) ? '' : $_POST['post_title'];
		$post_content	= empty($_POST['post_content']) ? '' : $_POST['post_content'];
		$mh_board_update->post_data = array(
			'ID'			=> $_POST['post_id'],
			'post_author'   => $user_id,
			'post_title'    => $post_title,
			'post_content'  => $post_content,
			'post_type'     => 'board',
			'tags_input'    => $tags,
			'post_status'   => 'publish'
		);
		$author = get_post_meta($_POST['post_id'],'guest_info',true);
		$update = false;
		if($author){
			if($_POST['guest_password'] && $_POST['guest_password'] == $author['guest_password']){
				$update = true;
			}else{
				$update = false;
				$mh_error->msg = "비밀번호를 확인해주세요.";
			}
		}else if(get_current_user_id()){
			$update = true;
		}else{
			$update = true;
		}
		
		if($_POST['post_open'] == 0 && $_POST['post_password']){
			$mh_board_update->post_data['post_password'] = $_POST['post_password'];
		}
		$mh_board_update->post_term = array(
			'terms' => array(intval($_POST['board_category'])),
			'taxonomy' => 'board_cat'
		);
		$term = get_term_by('id',$_POST['board_category'],'board_cat');
		if($update){
			if($mh_board_update->update_post()){
				wp_redirect(mh_get_board_link_by_board_cat($_POST['board_category']));
			}else{
				
			}
		}
	}else if(@$_REQUEST['mh_action'] == 'delete' && wp_verify_nonce($_REQUEST['_mh_board_nonce'],'mh_board_nonce')){
		$post_id = $_POST['post_id'];
		$post = get_post($post_id);
		$guest_info = get_post_meta($post_id,'guest_info',true);
		if((get_current_user_id() == $post->post_author && get_current_user_id() > 0) || (isset($_POST['guest_password']) && $_POST['guest_password'] == $guest_info['guest_password'])){
			$args = array(
				'ID' => $post_id,
				'post_status'   => 'trash'
			);
			if(wp_update_post($args)){
				echo "<script type='text/javascript'>location.href='".get_post_type_archive_link('board')."';</script>";
			}
		}else{
			$mh_error->msg = "비밀번호를 확인해주세요.";
		}
	}
}
?>