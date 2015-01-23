jQuery(document).ready(function() {
	fieldSettings["legull_tos"] = ".legull_tos_disable_submit,.rules_setting,.admin_label_setting, .error_message_setting, .css_class_setting, .visibility_setting";
	fieldSettings["legull_tos_accept"] = ".legull_tos_disable_submit,.rules_setting,.admin_label_setting, .error_message_setting, .css_class_setting, .visibility_setting";
	fieldSettings["legull_link"] = ".css_class_setting, .visibility_setting";
	jQuery(document).bind("gform_load_field_settings", function(event, field, form){
		jQuery("#field_legull_tos_accept").attr("checked", field["legull_tos_accept"] == true);
		jQuery("#field_legull_tos_accept_value").val(field["legull_tos_accept"]);
	});
});