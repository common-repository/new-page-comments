<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}
global $post;
$post_slug=$post->post_name;
$post_id = $post->ID;
$settings = NPCMNT_ADMIN::get_options_data('', true);
$commentCount = get_comments_number($post_id);
$commentCountText = $commentCount." comments";
 if($commentCount==0 || $commentCount==1){
 	$commentCountText = $commentCount." comment";
 }
 $postName = $post->post_name;
?><div class="npcmnt-wrap" id="npc-comments">
	<?php 
	if( $settings['comment-load-type']== 'new_page' ){
		$slug = sanitize_title(NPCMNT_ADMIN::get_options_data('slug'));
		if(NPCMNT_ADMIN::get_options_data('need_read_comment')=='true'){ 
			?>
		<a class='load-comment' href='<?php echo esc_url(home_url('/'.$slug.'/'.$postName)); ?>' data-postid='<?php echo esc_attr($post_id); ?>'><span> <?php echo esc_html__('READ', 'new-page-comments')." ".esc_html($commentCountText); ?> </span></a>
	<?php } ?>
		<a class='load-comment' href='<?php echo esc_url(home_url('/'.$slug.'/'.$postName)); ?>' data-postid='<?php echo esc_attr($post_id); ?>'><span"><?php echo esc_html__(NPCMNT_ADMIN::get_options_data('comment-btn-txt'), 'new-page-comments'); ?></span></a>
	<?php 
	}else{
		echo "<span class='npc-lazy-load-comment'>
				<span class='load-comment' data-postid='".esc_attr( $post_id )."'>".esc_html__('Show', 'new-page-comments')." ".$commentCountText." </span>
			</span>";
	}
	?>
  </div><?php
