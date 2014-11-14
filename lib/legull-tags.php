<?php

function legull_enqueue_scripts(){
	wp_enqueue_script( 'jquery-readmore', LEGULL_URL . 'asset/readmore.min.js', array('jquery'), '1.0', true );
	wp_enqueue_script( 'legull', LEGULL_URL . 'asset/script.js', array('jquery'), '1.0', true );
	wp_enqueue_style( 'legull', LEGULL_URL . 'asset/style.css' );
}

function legull_generate_documents_to_import(){
	$docs = apply_filters( 'legull_copy_documents_to_uploads/list', glob( LEGULL_PATH . "docs/*.md") );
	include_once( LEGULL_PATH . 'lib/parsedown.php' );
	$Parsedown = new Parsedown();
	foreach ($docs as $filename) {
		$import_file = basename($filename);
		$content = file_get_contents( $filename );
		$check_if_exists = new WP_Query( array( 'meta_key' => 'legull_file', 'meta_value' => $import_file, 'post_type' => LEGULL_CPT ) );		

		$import_post = array(
			'post_type' => LEGULL_CPT,
			'post_status' => 'publish',
			'ping_status' => 'closed',
			'comment_status' => 'closed'
			);
		if( count( $check_if_exists->posts ) ){
			$import_post['ID'] = $check_if_exists->posts[0]->ID;
		}

		// setup defaults
		$post_title = $import_file;

		if( has_shortcode( $content, 'legull_var' ) ) {
			$pattern = get_shortcode_regex();
			preg_match_all( '/'. $pattern .'/s', $content, $matches );

			// include space because attributes are not trimmed
			// set page title/name
			if( in_array( ' name="title"', $matches[3] ) ){
				$post_title = $matches[5][0];
			}

			// clean the content from [legull_var]
			$content = legull_strip_shortcode( $content, 'legull_var' );
		}

		if( has_shortcode( $content, 'legull' ) ) {
			// $pattern = get_shortcode_regex();
			// preg_match_all( '/'. $pattern .'/s', $content, $matches );

			// // include space because attributes are not trimmed
			// // set page title/name
			// if( in_array( ' name="title"', $matches[3] ) ){
			// 	$post_title = $matches[5][0];
			// }

			// // clean the content from [legull_var]
			// $content = legull_strip_shortcode( $content, 'legull_var' );
		}
		

		$import_post['post_title'] = $post_title;
		$import_post['post_name'] = $post_title;
		$import_post['post_content'] = $Parsedown->text( $content );
		$document_id = wp_insert_post( $import_post );
		update_post_meta( $document_id, 'legull_file', $import_file );
	}
	return true;
}

function legull_seek_option($haystack, $needle){
	$output='';
  foreach($haystack as $key => $value){
    if($key == $needle){
      $output = $value;
    }elseif(is_array($value)){
      $output = legull_seek_option($value, $needle);
    }
  }
  return $output;
}

function legull_shortcode_legull( $atts, $content = null ){
	global $legull;
	$a = shortcode_atts( array(
	    'field' => ''
	), $atts );

print_r($legull->getValue('ownership','owner_name'));
	// getValue();
	$options = (array) get_option('Legull');
	// if( !empty($options)){
	// 	// $pluck = wp_list_pluck($options, $a['field']);
	// 	echo '<pre>';
	// 	echo legull_seek_option($options, $a['field']);
	// 	// print_r($a['field']);
	// 	// print_r($pluck);
	// 	// print_r($options);
	// 	echo '</pre>';
	// }
	// print_r($a);
	return legull_seek_option($options, $a['field']);
	// return array( $a['field'] => 'sweet' );
}
add_shortcode( 'legull', 'legull_shortcode_legull' );

function legull_shortcode_legull_var( $atts, $content = null ){
	$a = shortcode_atts( array(
	    'title' => 'default'
	), $atts );
	return array( $a['title'] => $content );
}
add_shortcode( 'legull_var', 'legull_shortcode_legull_var' );

function legull_strip_shortcode($content, $shortcode){
    global $shortcode_tags;

    $stack = $shortcode_tags;
    $shortcode_tags = array($shortcode => 1);
    $content = strip_shortcodes($content);
    $shortcode_tags = $stack;
    
    return $content;
}
