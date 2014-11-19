<?php

add_action( 'admin_footer', 'legull_footer_admin_modal' );
function legull_footer_admin_modal() {
	?>
	<div id="LegullGenerateSuccess" style="display:none;">
		<?php _e( 'Successfully generated the legal documents for your site.', 'legull' ); ?>
	</div>
<?php
}

add_action( 'wp_ajax_legull_generate_documents', 'legull_ajax_generate_documents' );

function legull_ajax_generate_documents() {
	global $wpdb;

	$response = (object) array(
		'status' => false
	);

	if ( legull_generate_documents_to_import() ) {
		$response->status = true;
	}

	echo json_encode( $response );

	die();
}