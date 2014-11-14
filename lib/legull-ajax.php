<?php

add_action( 'wp_ajax_legull_generate_documents', 'legull_ajax_generate_documents' );

function legull_ajax_generate_documents() {
	global $wpdb;

	$response = (object) array(
		'status' => false
		);

	if( legull_generate_documents_to_import() ){
		$response->status = true;
	}

	echo json_encode($response);

	die();
}