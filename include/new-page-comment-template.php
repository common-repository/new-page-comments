<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}
get_header();
	echo '<div class="npccmnt-content-area">
			<div class="site-main">
				<div class="container">
					<div class="">
						<div class="comment-wrap '.esc_attr( (is_admin_bar_showing() )?'admin-cc':'vv' ).'">
							<div class="comments-heading">
								<a href="'.esc_url(get_the_permalink()).'">
									<i class="icon-arw"></i>
									<span>BACK TO STORY</span>
								</a>
								<h2>'.esc_html(get_the_title()).'</h2>
							</div>
						</div>';?>
						<div class="npccmnt-latest">
							<h3>Latest Articles</h3>
		<?php
	    $args=array(
	    'post_status' => 'publish',
	    'posts_per_page' =>4,
	     );     
		$my_query = new WP_Query($args);
	    if( $my_query->have_posts() ) :?>
	        <div class="crp">
	        <?php while ($my_query->have_posts()) : $my_query->the_post(); ?>
				<div class="crp-posts">
					<?php if ( has_post_thumbnail()) { ?>
					<div class="crp-pst">
						<a href="<?php esc_url(the_permalink()); ?>">
							<?php //$thumb_url = custom_post_thumbnail_resize(get_the_ID(), 140, 80);
								$thumb_url = get_the_post_thumbnail_url(get_the_ID(),'thumbnail');
								if(!empty($thumb_url)){
									echo '<img src="'.esc_url($thumb_url).'" alt="module-4-img">';
								}else{
									echo "";
								}
							?>
						</a>
					</div>
					<?php } ?>
					<div class="crp-info">
						<div class="crp-ca">
							<?php $category = get_the_category(); 
								echo esc_html($category[0]->cat_name);
							?>
						</div>
						<h4><a href="<?php esc_url(the_permalink()); ?>"><?php esc_html(the_title()); ?></a></h4>
					</div>
				</div>
		   <?php  endwhile; ?>
			</div><!-- /.latest-posts -->
	    <?php 
		endif; wp_reset_postdata(); ?>
	</div>
	<?php 
	//Load comment files
	$file = get_option('npc_load_comment');
	require_once $file;
	echo '</div></div></div></div>';
get_footer();

/*endwhile;*/