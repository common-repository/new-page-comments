<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Silence is golden.
}
class NPCMNT_ADMIN{
	/**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
	function __construct(){
		if(is_admin()){
			add_filter( 'plugin_action_links_' . NPCMNT_PLUGIN_BASE, array($this, 'add_action_links') );
			add_action('admin_menu', array( $this, 'settings_pages') );
			add_action( 'admin_init', array( $this, 'register_newpagecomment') );
            add_action('admin_enqueue_scripts', array($this, "admin_script_option_page"));
            add_action('wp_ajax_npc_send_email_query_message', array($this, 'npc_send_email_query_message'));

            add_filter('plugin_row_meta' , array($this, 'npc_add_plugin_meta_links'), 10, 2);
		}
	}

    public  function admin_script_option_page($hook){
        if($hook != 'settings_page_new-page-comment') {
            return ;
        }
        wp_enqueue_style( 'npc_admin_script', NPCMNT_URL.'assets/admin-main.css', array(), NPC_VERSION, 'all' );
        wp_enqueue_script( 'npc_admin_script', NPCMNT_URL.'assets/admin-main.js', array('jquery'), NPC_VERSION, true );
    }

	public static function get_options_data($setting_name=null, $single=false){
		$defaultValues = array(
                        'comment-load-type'=>'new_page',
						'comment-btn-txt'=> 'Post a comment',
						'slug' => 'comments',
						'need_read_comment'=> 'true',
						);
		$options = get_option( 'new-page-comment-opt' );
		$options = wp_parse_args($options, $defaultValues);
		if(!empty($setting_name)){
			return $options[$setting_name];
		}
        if($single){
            return $options;
        }else{
    		return '';
        }
	}
	function add_action_links($links){
		$mylinks = array(
		 '<a href="' . admin_url( 'options-general.php?page=new-page-comment' ) . '">Settings</a>',
		 );
		return array_merge( $links, $mylinks );
	}

	function settings_pages(){
	    add_submenu_page('options-general.php', 
            esc_html__('New page comment', 'new-page-comments'), 
            esc_html__('New page comment', 'new-page-comments'), 
            'manage_options', 'new-page-comment', array($this, 'settings_page') );
	}
    function npc_admin_link($tab = '', $args = array()){
        $page = 'new-page-comment';
        if ( ! is_multisite() ) {
            $link = admin_url( 'options-general.php?page=' . $page );
        }
        else {
            $link = admin_url( 'options-general.php?page=' . $page );
        }

        if ( $tab ) {
            $link .= '&tab=' . $tab;
        }

        if ( $args ) {
            foreach ( $args as $arg => $value ) {
                $link .= '&' . $arg . '=' . urlencode( $value );
            }
        }

        return esc_url($link);
    }


    function npc_get_tab( $default = '', $available = array() ) {

        $tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : $default;
            
        if ( ! in_array( $tab, $available ) ) {
            $tab = $default;
        }

        return $tab;
    }

	function settings_page(){
        if(!current_user_can('manage_options')){
            return ;
        }
		$this->options = self::get_options_data(null, true);//get_option( 'new-page-comment-opt' );
        $tab = $this->npc_get_tab('general', array('general','help'));
		?>
		<div class="npc-main-wrapper wrap">
			<h2><?php echo esc_html__("New page comments", 'new-page-comments'); ?></h2>
            <br/>
            <div class="nav-tab-wrapper npc-main-wrapper">
                <?php
                echo '<a href="' . esc_url($this->npc_admin_link('general')) . '" class="nav-tab ' . esc_attr( $tab == 'general' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-dashboard"></span> ' . esc_html__('General', 'new-page-comments') . '</a>';
                echo '<a href="' . esc_url($this->npc_admin_link('help')) . '" class="nav-tab ' . esc_attr( $tab == 'help' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-editor-help"></span> ' . esc_html__('Help','npc-for-wp') . '</a>';
                ?>
            </div>
			<form method="post" action="options.php"> 
                <div class="form-wrap form-field-wrapper">
				<?php 
				settings_fields( 'new-page-comment-group' );
                echo "<div class='npc-general' ".( $tab != 'general' ? 'style="display:none;"' : '').">";
				do_settings_sections( 'new-page-comment-page' );
                echo "</div>";
                echo "<div class='npc-help' ".( $tab != 'help' ? 'style="display:none;"' : '').">";
                    $this->help_section_contents(); 
                echo "</div>";
				submit_button(); ?>
                </div>
			</form>
			</div>
			</form>
		</div>
		<script>
			var checkbox_operation = function(e){
				var id = e.getAttribute('data-id');
				if(e.checked){
					document.getElementById(id).value = 'true';
				}else{
					document.getElementById(id).value = 'false';
				}
			}
		</script>
			<?php
	}
	function register_newpagecomment(){
		 register_setting(
            'new-page-comment-group', // Option group
            'new-page-comment-opt', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            esc_html__('','new-page-comments'), // Title
            array( $this, 'print_section_info' ), // Callback
            'new-page-comment-page' // Page
        );  

        add_settings_field(
            'new_cmnt_lazy_load', // ID
            esc_html__('Comment lazy load','new-page-comments'), // Title 
            array( $this, 'cmnt_lazy_load_callback' ), // Callback
            'new-page-comment-page', // Page
            'setting_section_id' // Section           
        );

        add_settings_field(
            'new_cmnt_btn', // ID
            esc_html__('Comment button','new-page-comments'), // Title 
            array( $this, 'new_cmnt_btn_callback' ), // Callback
            'new-page-comment-page', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'comment_slug', 
            esc_html__('comment slug','new-page-comments'), 
            array( $this, 'comment_slug_callback' ), 
            'new-page-comment-page', 
            'setting_section_id'
        );
        add_settings_field(
            'need_read_comment_link', 
            esc_html__('Show Read comment button','new-page-comments'), 
            array( $this, 'need_read_comment_link_callback' ), 
            'new-page-comment-page', 
            'setting_section_id'
        );      
	}

	/**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['comment-load-type'] ) )
            $new_input['comment-load-type'] = sanitize_text_field( $input['comment-load-type'] );

        if( isset( $input['comment-btn-txt'] ) )
            $new_input['comment-btn-txt'] = sanitize_text_field( $input['comment-btn-txt'] );

        if( isset( $input['slug'] ) )
            $new_input['slug'] = sanitize_title( $input['slug'] );

        if( isset( $input['need_read_comment'] ) )
            $new_input['need_read_comment'] = sanitize_text_field( $input['need_read_comment'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print esc_html__('','new-page-comments');
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function cmnt_lazy_load_callback()
    {
        if(!isset( $this->options['comment-load-type'] ) ){
            $this->options['comment-load-type'] = 'new_page';
        }
        printf(
            '<label><input type="radio" id="comment-lazy-load-switch" name="new-page-comment-opt[comment-load-type]" value="lazy_load" %s />%s</label>',
            isset( $this->options['comment-load-type'] ) && $this->options['comment-load-type']=='lazy_load'? "checked" : '',
            esc_html__('Load comment via Ajax on same page','new-page-comments')
        );

        printf(
            '<label><input type="radio" id="comment-new-page-switch" name="new-page-comment-opt[comment-load-type]" value="new_page" %s />%s</label>',
            isset( $this->options['comment-load-type'] ) && $this->options['comment-load-type']=='new_page'? "checked" : '',
            esc_html__('Load comment on new page','new-page-comments')
        );
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function cmnt_new_page_load_callback()
    {
        
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function new_cmnt_btn_callback()
    {
        printf(
            '<input type="text" id="comment-btn-txt" name="new-page-comment-opt[comment-btn-txt]" value="%s" />',
            isset( $this->options['comment-btn-txt'] ) ? esc_attr( $this->options['comment-btn-txt']) : esc_html__('Post a comment','new-page-comments')
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function comment_slug_callback()
    {
        printf(
            '<input type="text" id="slug" name="new-page-comment-opt[slug]" value="%s" />',
            isset( $this->options['slug'] ) ? esc_attr( $this->options['slug']) : esc_attr('comments')
        );
    }

    public function need_read_comment_link_callback()
    {
        printf(
            '<input type="checkbox" class="perform_Checkbox" data-id="need_read_comment" onclick="checkbox_operation(this)"  value="true" %s/>',
            isset( $this->options['need_read_comment'] ) && $this->options['need_read_comment']=='true' ?  'checked':'' 
        );
        echo "<input type='hidden' id='need_read_comment' name='new-page-comment-opt[need_read_comment]' value='". (isset( $this->options['need_read_comment'] )? 'true': 'false') ."'>";
    }
    public function help_section_contents(){
        echo "<div class='help-section' style='margin-top:50px'><h3>".esc_html__('Help Section', 'new-page-comments')."</h3>";
        ?>  
        <hr />  
            <div class="npc_contact_us_div">
                <strong><?php echo esc_html__('If you have any query or request for feature, please write the query in below box or email us at', 'new-page-comments') ?> <a href="mailto:team@easetousers.com">team@easetousers.com</a>. <?php echo esc_html__('We will reply to your email address shortly', 'new-page-comments') ?></strong>
                <hr />  
                <ul>
                    <li><label for="npc_query_message"><?php echo esc_html__('Message', 'new-page-comments'); ?></label>
                        <textarea rows="5" cols="60" id="npc_query_message" name="npc_query_message"> </textarea>
                        <br>
                        <p class="npc-query-success npc_hide"><?php echo esc_html__('Message sent successfully, Please wait we will get back to you shortly', 'new-page-comments'); ?></p>
                        <p class="npc-query-error npc_hide"><?php echo esc_html__('Message not sent. please check your network connection', 'new-page-comments'); ?></p>
                    </li> 
                    <li><button class="button npc-send-query"><?php echo esc_html__('Send Message', 'new-page-comments'); ?></button></li>
                </ul>            
                       
            </div>
            </div>
    <?php
    }

    function npc_send_email_query_message(){

        if ( ! isset( $_POST['npc_security_nonce'] ) ){
            echo json_encode(array('status'=>'f', 'message'=> 'Security nonce is empty'));
            die();
        }
        if ( !wp_verify_nonce( $_POST['npc_security_nonce'], 'npc_ajax_check_nonce' ) ){
           echo json_encode(array('status'=>'f', 'message'=> 'Security nonce not matched'));
           die();
        }
        
        $message    = sanitize_textarea_field($_POST['message']);        
        $message .= "<table>
                        <tr><td>Plugin</td><td>New Page Comments</td></tr>
                        <tr><td>Version</td><td>".NPC_VERSION."</td></tr>
                    </table>";
        $user       = wp_get_current_user();
        
        if($user){
            
            $user_data  = $user->data;        
            $user_email = $user_data->user_email;       
            //php mailer variables
            $to = 'team@easetousers.com';
            $subject = "New Page Comments Customer Query";
            $headers = 'From: '. esc_attr($user_email) . "\r\n" .
            'Reply-To: ' . esc_attr($user_email) . "\r\n";
            // Load WP components, no themes.                      
            $sent = wp_mail($to, $subject, strip_tags($message), $headers);        
            
            if($sent){
            echo json_encode(array('status'=>'t'));            
            }else{
            echo json_encode(array('status'=>'f'));            
            }
            
        }
                        
           wp_die();        
    }

    function npc_add_plugin_meta_links($meta_fields, $file) {
    
        if ( NPCMNT_PLUGIN_BASE == $file ) {
          $plugin_url = "https://wordpress.org/support/plugin/new-page-comments/";   
          $hire_url = "";
          $meta_fields[] = "<a href='" . esc_url($plugin_url) . "' target='_blank'>" . esc_html__('Support Forum', 'new-page-comments') . "</a>";
          $meta_fields[] = "<a href='" . esc_url($plugin_url) . "/reviews#new-post' target='_blank' title='" . esc_html__('Rate', 'new-page-comments') . "'>
                <i class='npc-p-rate-stars'>"
            . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
            . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
            . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
            . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
            . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
            . "</i></a>";            
        }

        return $meta_fields;
        
      }
}
$npc_cmnt_admin = new NPCMNT_ADMIN();