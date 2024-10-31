<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}
class NPC_class{
	public function init(){
		add_filter('comments_template', array($this, 'new_page_load_comment_section'), 999);
		add_action( 'wp_enqueue_scripts', array($this, 'load_script_style') );
		require_once NPCMNT_PATH . "include/settings.php";


		add_action( 'init',  array($this, 'new_page_load_prefix_movie_rewrite_rule'), 30) ;
		add_filter( 'query_vars',  array($this, 'new_page_load_prefix_register_query_var'), 20, 1);
		add_filter( 'template_include', array($this, 'new_page_load_prefix_url_rewrite_templates'), 1, 1); 
		add_filter( 'wp_ajax_new_page_comment_template', array($this, 'new_page_comment_template'), 10); 
	}

	public function new_page_comment_template(){
		$id = $_POST['id'];
		/*$post = get_post($id, OBJECT );
		setup_postdata( $post );*/
		$file = get_option('npc_load_comment');


		$args=array(
	    'post_status' => 'publish',
	    'p' =>$id,
	     );     
		$my_query = new WP_Query($args);
	    if( $my_query->have_posts() ) :
	    	while ($my_query->have_posts()) : $my_query->the_post();
	    		ob_start();
				require_once $file;
				$commentTemplate = ob_get_contents();
				ob_clean();
	    	endwhile;
	    endif;wp_reset_postdata();

		
		//echo $commentTemplate;die;
		echo json_encode(array('status'=>200, 'comments'=>$commentTemplate));
		die;
	}

	public function new_page_load_comment_section($file){
		update_option('npc_load_comment', $file);
		$file = NPCMNT_PATH . 'include/comment-load-template.php';
		 return $file;
	}


	function load_script_style() {
		if ( (is_singular() || get_query_var( 'npte' )=='load_comments' ) && comments_open() && get_option( 'thread_comments' ) ) {
	    	wp_enqueue_style( 'npcmnt-comment', NPCMNT_URL . '/assets/cmnt.css', array(), '1.1', 'all');
	    	wp_enqueue_script( 'npcmnt-comment-script', NPCMNT_URL . '/assets/npc-cmnt.js', array('jquery'), '1.1', 'all');
	    	$data = array(
	    				'ajax_url'=>admin_url( 'admin-ajax.php' ),
	    				);
	    	wp_localize_script( 'npcmnt-comment-script', 'npc_vars', $data );
	    }
	}

	function new_page_load_prefix_movie_rewrite_rule() {
		$slug = sanitize_title(NPCMNT_ADMIN::get_options_data('slug'));
		add_rewrite_rule( '^'.$slug.'\/([^/]+)\/?$', 'index.php?cp_slug=$matches[1]&npte=load_comments', 'top' );
		flush_rewrite_rules( false );
	}


	function new_page_load_prefix_register_query_var( $vars ) {
		$vars[] = 'cp_slug';
		$vars[] = 'npte';
		return $vars;
	}

	function npc_load_comment_ajax_load(){
		$filename = get_option('npc_load_comment');
		require_once $filename;
	}


	function new_page_load_prefix_url_rewrite_templates($template){
		if ( get_query_var( 'npte' )=='load_comments' ) {
			add_action('parse_query', array($this, 'comment_parse_query'));
		//remove_action('wp_head', 'saswp_data_generator');
			$template = NPCMNT_PATH . '/include/new-page-comment-template.php';
		}
		return $template;
	}

	function comment_parse_query($query){
		if(is_main_query()){
			$commentPost = get_query_var( 'cp_slug' );
			$query->post_name = $commentPost;
			$query->post_type = 'any';
			$query->is_single = true;
		}
	}

}