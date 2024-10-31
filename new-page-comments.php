<?php
/**
 Plugin Name: New Page Comments
 Description: Show comments section in new page or load when user wants to see. Reduce load time process.
 Author: easetousers
 Author URI: http://easetousers.com
 Version: 0.3
 Donate link: https://paypal.me/Ajeet260/25
 License: GPL2+
 Text Domain: new-page-comments
 Domain Path: /languages/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

define( 'NPC_VERSION', '0.3' );

define( 'NPCMNT__FILE__', __FILE__ );
define( 'NPCMNT_PLUGIN_BASE', plugin_basename( NPCMNT__FILE__ ) );
define( 'NPCMNT_PATH', plugin_dir_path( NPCMNT__FILE__ ) );
define( 'NPCMNT_URL', plugins_url( '/', NPCMNT__FILE__ ) );


add_action("plugins_loaded", 'npc_load_init');
function npc_load_init(){
	require_once NPCMNT_PATH.'include/plugin.php';
	$obj = new NPC_class();
	$obj->init();
}
