<?php

function legull_enqueue_scripts(){
	wp_enqueue_style( 'legull', LEGULL_URL . 'assets/style.css' );
}

function legull_generate_documents_to_import(){
	$docs = apply_filters( 'legull_copy_documents_to_uploads/list', glob( LEGULL_PATH . "docs/*.md") );
	foreach ($docs as $filename) {
		$import_file = basename($filename);
		$content = file_get_contents( $filename );
		$check_if_exists = new WP_Query( array( 'meta_key' => 'legull_file', 'meta_value' => $import_file, 'post_type' => LEGULL_CPT ) );

		$import_post = array(
			'post_content' => $content,
			'post_type' => LEGULL_CPT,
			'post_status' => 'publish',
			'ping_status' => 'closed',
			'comment_status' => 'closed'
			);
		if( count( $check_if_exists->posts ) ){
			$import_post['ID'] = $check_if_exists->posts[0]->ID;
		}
		if( has_shortcode( $content, 'legull_var' ) ) {
			$pattern = get_shortcode_regex();
			preg_match_all( '/'. $pattern .'/s', $content, $matches );
			// include space because attributes are not trimmed
			if( in_array( ' name="title"', $matches[3] ) ){
				$import_post['post_title'] = $matches[5][0];
			} else {
				$import_post['post_title'] = '';
			}
		} else {
			$import_post['post_title'] = $import_file;
		}
		$import_post['post_name'] = $import_post['post_title'];
		$document_id = wp_insert_post( $import_post );
		update_post_meta( $document_id, 'legull_file', $import_file );
	}
	return true;
}

function legull_shortcode_legull_var( $atts, $content = null ){
	$a = shortcode_atts( array(
	    'title' => 'default'
	), $atts );
	return array( $a['title'] => $content );
}
add_shortcode( 'legull_var', 'legull_shortcode_legull_var' );