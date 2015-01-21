<?php

class Legull_GravityForms {
	function __construct(){
		add_filter( 'gform_add_field_buttons', array( $this, 'add_field_button' ) );
	}
	function add_field_button( $field_groups ){
		foreach( $field_groups as &$group ){
			if( $group["name"] == "advanced_fields" ){
				$group["fields"][] = array(
					"class"=>"button",
					"value" => __('Terms of Service', 'legull'),
					"onclick" => "StartAddField('legull_tos');"
				);
				$group["fields"][] = array(
					"class"=>"button",
					"value" => __('Legull Terms Link', 'legull'),
					"onclick" => "StartAddField('legull_link');"
				);
				break;
			}
		}
		return $field_groups;
	}
}