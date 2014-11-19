var $info;
var legull_ajax_generate_doc_request = true;
function legull_ajax_generate_docs(e) {
	e.preventDefault();
	if (legull_ajax_generate_doc_request) {
		legull_ajax_generate_doc_request = false;
		jQuery.post(ajaxurl, {
			'action': 'legull_generate_documents'
		}, function (response) {
			response = JSON.parse(response);
			legull_ajax_generate_doc_request = true;
			if (response.status)
				$info.dialog('open');
		});
	}
}

jQuery(document).ready(function ($) {
	$info = $("#LegullGenerateSuccess");
	$info.dialog({
		'dialogClass'  : 'wp-dialog',
		'modal'        : true,
		'autoOpen'     : false,
		'closeOnEscape': true,
		'buttons'      : {
			"Close": function () {
				$(this).dialog('close');
			}
		}
	});
	$("#open-modal").click(function (event) {
		event.preventDefault();
		$info.dialog('open');
	});
	$('#legull_ajax_generate_docs').on("click", function (e) {
		e.preventDefault();
		legull_ajax_generate_docs(e);
	});
	$('.postbox-container .postbox .inside p').readmore({
		speed    : 75,
		maxHeight: 55
	});
});