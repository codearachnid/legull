<?php
/*
Plugin Name: Legull
Plugin URI:
Description: 
Version: 1.0
Author: Legull LLC, Timothy Wood (@codearachnid), Chris Gatewood
Author URI: http://www.legull.com
Text Domain: legull
*/

// include_once( LEGULL_PATH . 'lib/parsedown.php' );
// $Parsedown = new Parsedown();
// echo $Parsedown->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>

define('LEGULL_PATH', trailingslashit( dirname( __FILE__ ) ) );
define('LEGULL_URL', plugins_url( '/', __FILE__) );
define('LEGULL_CPT', 'legull_documents' );

if ( ! class_exists( 'AdminPageFramework' ) )
    include_once( LEGULL_PATH . 'lib/admin-page-framework.min.php' );

function legull_plugin_loaded(){
	global $legull;

	include_once( LEGULL_PATH . 'lib/legull-tags.php' );

	if( !class_exists( 'Legull_Conf' ) )
		include_once( LEGULL_PATH . 'lib/legull-conf.php' );

	if( !class_exists( 'Legull' ) )
		include_once( LEGULL_PATH . 'lib/legull.php' );	
	new Legull;

	include( LEGULL_PATH . 'lib/legull-cpt.php' );
		new Legull_CustomPostType( LEGULL_CPT, null, __FILE__ );

	include_once( LEGULL_PATH . 'lib/legull-ajax.php' );

	if( !class_exists( 'Legull_MetaBox_For_Dashboard' ) )
		include_once( LEGULL_PATH . 'lib/legull-metabox-for-dashboard.php' );
	new Legull_MetaBox_For_Dashboard(
		'legull_metabox_for_dashboard',
		__( 'Legull 1.0.0', 'legull' ),
		'legull_dashboard',
		'side',
		'default'
	);

	if( !class_exists( 'Legull_MetaBox_For_Active_Documents' ) )
		include_once( LEGULL_PATH . 'lib/legull-metabox-for-active-documents.php' );
	new Legull_MetaBox_For_Active_Documents(
		'legull_metabox_for_active_documents',
		__( 'Available Legull Documents', 'legull' ),
		'legull_settings',
		'side',
		'default'
	);
	remove_filter( 'update_footer', array( 'AdminPageFramework_PageLoadInfo_Page', '_replyToGetPageLoadInfo' ), 1000 );
	add_action( 'admin_enqueue_scripts', 'legull_enqueue_scripts' );
}

add_action( 'plugins_loaded', 'legull_plugin_loaded' );
register_activation_hook( __FILE__, 'legull_copy_documents_to_uploads' );
