<?php

class Legull_GravityForms {
	private $fields = array();

	function __construct(){
		$this->fields[] = (object) array(
			'id' => 'legull_tos',
			'label' => __('Display Terms', 'legull'),
			'class' => 'button'
			);
		$this->fields[] = (object) array(
			'id' => 'legull_tos_accept',
			'label' => __('Accept Terms', 'legull'),
			'class' => 'button'
			);
		$this->fields[] = (object) array(
			'id' => 'legull_link',
			'label' => __('Link to Terms', 'legull'),
			'class' => 'button'
			);
		add_filter( 'gform_add_field_buttons', array( $this, 'add_field_button' ) );
		add_filter( 'gform_field_type_title', array( $this, 'field_type_title' ) );
		add_filter( 'gform_field_content', array( $this, 'field_content' ), 10, 5);
		add_action( 'gform_field_standard_settings' , array( $this, 'field_standard_settings' ), 10, 5 );
		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_editor_js', array( $this, 'editor_js' ) );

	}
	function add_field_button( $field_groups ){
		foreach( $field_groups as &$group ){
			if( $group["name"] == "advanced_fields" ){
				foreach( $this->fields as $field ){
					$group["fields"][] = array(
						"class"=> $field->class,
						"value" => $field->label,
						"onclick" => sprintf( "StartAddField('%s');",  $field->id )
					);	
				}
				break;
			}
		}
		return $field_groups;
	}

	function field_type_title( $type ){
		$labels = wp_list_pluck( $this->fields, 'label', 'id' );
		if( array_key_exists( $type, $labels ) ){
			return $labels[ $type ];
		}
	}

	function field_content($field_content, $field, $value, $lead_id, $form_id){
		$labels = wp_list_pluck( $this->fields, 'label', 'id' );
		if( array_key_exists( $field['type'], $labels ) ){
			$field_content = sprintf("<div class='gfield_admin_icons'><div class='gfield_admin_header_title'>%s</div><a class='field_delete_icon' id='gfield_delete_{$field["id"]}' title='%s' href='#' onclick='StartDeleteField(this); return false;'><i class='fa fa-times fa-lg'></i></a><a class='field_duplicate_icon' id='gfield_duplicate_{$field["id"]}' title='%s' href='#' onclick='StartDuplicateField(this); return false;'><i class='fa fa-files-o fa-lg'></i></a><a class='field_edit_icon edit_icon_collapsed' title='%s'><i class='fa fa-caret-down fa-lg'></i></a></div><label class='gfield_label' for='input_{$field["id"]}'>%s<span class='gfield_required'></span></label><div class='ginput_container'>%s</div><div class='gfield_description'>%s</div>",
				$labels[ $field['type'] ] . ' : ' . __( 'Field ID', 'legull' ) . ' ' . $field["id"],
				__( 'Click to delete this field', 'legull' ),
				__( 'Click to duplicate this field', 'legull' ),
				__( 'Click to expand and edit the options for this field', 'legull' ),
				$this->field_label( $field, $form_id ),
				$this->field_container( $field, $form_id ),
				$this->field_description( $field, $form_id )
				);
		}
	    return $field_content;
	}

	function field_standard_settings( $position, $form_id ){
		// Create settings on position 50 (right after Field Label)
		if( $position == 50 ){
		?>
		<li class="legull_tos_accept_setting field_setting">
			<input type="checkbox" id="field_tos" onclick="SetFieldProperty(‘field_tos’, this.checked);" />
			<label for="field_tos" class="inline">
				<?php _e("Disable Submit Button", "legull"); ?>
				<?php gform_tooltip("form_field_tos"); ?>
			</label>
		</li>
		<?php
		}
	}

	function tooltips($tooltips){
		$tooltips["form_field_legull_tos_accept"] = "<h6>Disable Submit Button</h6>Check the box if you would like to disable the submit button.";
		$tooltips["form_field_default_value"] = "<h6>Default Value</h6>Enter the Terms of Service here.";
		return $tooltips;
	}

	function editor_js(){
		?>
<script type='text/javascript'>
jQuery(document).ready(function() {
	fieldSettings["legull_tos_accept"] = ".legull_tos_accept_setting,.admin_label_setting, .error_message_setting, .css_class_setting, .visibility_setting";
	jQuery(document).bind("gform_load_field_settings", function(event, field, form){
		jQuery("#field_legull_tos_accept").attr("checked", field["legull_tos_accept"] == true);
		jQuery("#field_legull_tos_accept_value").val(field["legull_tos_accept"]);
	});
});
</script>
<?php
	}

	function field_label( $field, $form_id, $field_content = '' ){
		switch( $field['type'] ){
			case 'legull_tos':
				$field_content = __( 'Terms & Conditions.', 'legull' );
				break;
		}
		return $field_content;
	}

	function field_container( $field, $form_id, $field_content = '' ){
		$css = isset( $field['cssClass'] ) ? $field['cssClass'] : '';
		$input_name = $form_id .'_' . $field["id"];
		switch( $field['type'] ){
			case 'legull_tos':
				$tab_index = GFCommon::get_tabindex();
				$field_content = sprintf( "<textarea readonly class='%s' $tab_index>%s</textarea>", 
						$field["type"] . ' ' . esc_attr($css),
						'legull content goes here');
				break;
			case 'legull_tos_accept':
				$tab_index = GFCommon::get_tabindex();
				$field_content = sprintf( "<input disabled='disabled' type='checkbox' name='input_%s' id='%s' class='%s' $tab_index cols='50' rows='10' /> %s %s.", 
						$field["id"], 
						$field['type'] . '-'.$field['id'] , 
						$field["type"] . ' ' . esc_attr($css), 
						__( 'I have read and agree to the', 'legull' ),
						$this->link_to_terms() );
				break;
			case 'legull_link':
				$field_content = sprintf( "%s %s.", 
					__( 'Read the site', 'legull' ), 
					$this->link_to_terms() );
				break;
		}
		return $field_content;
	}

	function field_description( $field, $form_id, $field_content = '' ){
		return $field_content;
	}

	function link_to_terms(){
		return sprintf( "<a href='%s' target='_blank'>%s</a>", '#', __( 'Terms & Conditions', 'legull' ) );
	}

}