<?php

add_action( 'admin_footer', 'legull_ajax_javascript' );
add_action( 'wp_ajax_legull_generate_documents', 'legull_ajax_generate_documents' );

function legull_ajax_javascript() {
?>
<script type="text/javascript" >
var legull_ajax_generate_doc_request = true;
function legull_ajax_generate_docs(e){
	e.preventDefault();
	if( legull_ajax_generate_doc_request ){
		legull_ajax_generate_doc_request = false;
		jQuery.post(ajaxurl, {
			'action': 'legull_generate_documents'
		}, function(response) {
			legull_ajax_generate_doc_request = true;
			alert('Got this from the server: ' + response);
		});
	}
}
jQuery(document).ready(function() {
	jQuery('#legull_ajax_generate_docs').on( "click", function(e){
		e.preventDefault();
		legull_ajax_generate_docs(e);
	});
});
</script>
<?php
}


function legull_ajax_generate_documents() {
	global $wpdb; // this is how you get access to the database

	if( legull_generate_documents_to_import() ){
		echo 'success';
	} else {
		echo 'fail';
	}

	die(); // this is required to return a proper result
}