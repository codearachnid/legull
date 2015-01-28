<?php
/*
Plugin Name: Legull
Plugin URI: http://www.legull.com
Description: Legull is Terms of Service and Privacy Policy for your WordPress site, generated by simple questions and answers, editable and update-able.
Version: 1.1.2
Author: Legull LLC, Timothy Wood (@codearachnid), Chris Gatewood (@gatewood5000)
Author URI: http://www.legull.com
Text Domain: legull
Domain Path: /languages
Credits: 
	* UI field framework by Admin Page Framework
	* Background patterns from subtlepatterns.com
*/

global $legull;
define( 'LEGULL_PATH', trailingslashit( dirname( __FILE__ ) ) );
define( 'LEGULL_URL', plugins_url( '/', __FILE__ ) );
define( 'LEGULL_CPT', 'legull_terms' );

// if ( !class_exists( 'AdminPageFramework' ) ) {
// 	include_once( LEGULL_PATH . 'apf/admin-page-framework.php' );
// 	include_once( LEGULL_PATH . 'lib/admin-page-framework.revealer.php' );
// }

function legull_plugin_loaded() {
	global $legull;

	include_once( LEGULL_PATH . '/lib/admin-page-framework.php' );


	include_once( LEGULL_PATH . 'lib/legull-tags.php' );

	if ( !class_exists( 'Legull_Conf' ) ) {
		include_once( LEGULL_PATH . 'lib/legull-conf.php' );
	}

	if ( !class_exists( 'Legull' ) ) {
		include_once( LEGULL_PATH . 'lib/legull.php' );
	}
	$legull = new Legull;

	include( LEGULL_PATH . 'lib/legull-cpt.php' );
	new Legull_CustomPostType( LEGULL_CPT, null, __FILE__ );

	if ( !class_exists( 'Legull_MetaBox' ) ) {
		include_once( LEGULL_PATH . 'lib/legull-metabox.php' );
	}
	new Legull_MetaBox(
		'legull_metabox',
		sprintf( '<img src="%s" /> %s', legull_icon( 16 ), __( 'Legull', 'legull' ) ),
		array( 'legull_dashboard', 'legull_publish', 'legull_addons' ),
		'side',
		'default'
	);

	if( legull_integrated_plugins() ) {
		// ensure plugins have the appropriate integration points
		if( class_exists( 'GFCommon' ) ){
			include_once( LEGULL_PATH . 'lib/legull-gravityforms.php' );
			new Legull_GravityForms();
		}
		include_once( LEGULL_PATH . 'lib/legull-metabox-integrated-plugins.php' );
		new Legull_MetaBox_Integrated_Plugins(
			'legull_integrated_plugins',
			__( 'Integrated Plugins', 'legull' ),
			array( 'legull_dashboard' ),
			'side',
			'default'
		);
	}

	add_action( 'admin_enqueue_scripts', 'legull_enqueue_admin_scripts' );
	add_action( 'wp_enqueue_scripts', 'legull_enqueue_scripts' );
}

function legull_custom_activation_message_init(){
	add_filter( 'gettext', 'legull_custom_activation_message', 99, 3 );
}

add_action( 'plugins_loaded', 'legull_plugin_loaded' );
add_action( 'load-plugins.php', 'legull_custom_activation_message_init' );
