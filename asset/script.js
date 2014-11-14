var legull_ajax_generate_doc_request = true;
function legull_ajax_generate_docs(e){
	e.preventDefault();
	if( legull_ajax_generate_doc_request ){
		legull_ajax_generate_doc_request = false;
		jQuery.post(ajaxurl, {
			'action': 'legull_generate_documents'
		}, function(response) {
			response = JSON.parse( response );
			legull_ajax_generate_doc_request = true;
			alert('Got this from the server: ' + response.status);
		});
	}
}

jQuery(document).ready(function($){
	$('#legull_ajax_generate_docs').on( "click", function(e){
		e.preventDefault();
		legull_ajax_generate_docs(e);
	});
	$('.postbox-container .postbox .inside p').readmore({
		speed: 75,
		maxHeight: 55
	});
});