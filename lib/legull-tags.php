<?php

add_shortcode( 'legull', 'legull_shortcode' );
add_shortcode( 'legull_var', 'legull_shortcode_fake' );
add_shortcode( 'legull_part', 'legull_shortcode_fake' );
add_shortcode( 'legull_condition', 'legull_shortcode_fake' );

function legull_custom_activation_message( $translated_text, $untranslated_text, $domain ){
	$old_activation_message = array(
        "Plugin <strong>activated</strong>.",
        "Selected plugins <strong>activated</strong>." 
    );

    if ( in_array( $untranslated_text, $old_activation_message, true ) ){
        $translated_text = sprintf( '%s <b><a href="%s">%s</a></b>',
        		__('Thank you for activating Legull, your legal terms management solution.', 'legull'),
        		get_admin_url() . 'admin.php?page=legull_dashboard',
        		__('Get Started', 'legull')
        		);
        remove_filter( current_filter(), __FUNCTION__, 99 );
    }

	return $translated_text;
}

function legull_enqueue_scripts() {
	// add_thickbox();
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_style( "wp-jquery-ui-dialog" );
	wp_enqueue_script( 'jquery-readmore', LEGULL_URL . 'asset/readmore.min.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'legull', LEGULL_URL . 'asset/script.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_style( 'legull', LEGULL_URL . 'asset/style.css' );
}

function  legull_icon( $size = 16, $base64 = false ) {
	if ( $base64 ) {
		$file = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDUxMiA1MTIiIGhlaWdodD0iNTEycHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiB3aWR0aD0iNTEycHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxwYXRoIGQ9Ik00MDcuNjU4LDI1Ny4xOTFDNDQyLjA0NSwyMTEuODM4LDQzOC4yOTcsMTUxLjk3LDQ4MSw1My4yMzJDMjU0LjQwNyw1MC4yNDQsMTU2LjE2MywyMDYuNTQsMTE0LjM2MiwzNjMuMzcgIEwzMSw0NTguODExbDU5LjM4OS0xNi41NDZsNDcuODMzLTYzLjc4NmMxMzAuMzYzLTE2LjYzMSwyMzguMTc3LTg4Ljg3MywyMzguMTc3LTg4Ljg3M2wtNzAuMTU2LTUxLjQ5NyAgQzMwNi4yNDIsMjM4LjEwOCwzNjAuOTgsMjM0LjE5LDQwNy42NTgsMjU3LjE5MXogTTE3MC43NjQsMzI5Ljk0bC0xLjE5OC0xLjMyNGMxOC41ODktMjkuMjAxLDExOC43NS0xNzguOTI5LDI1NC4wNDYtMjM3LjY5OSAgQzM1OS4yMDUsMTI0Ljk2NywyMjUuNjg2LDI1OC44MjYsMTcwLjc2NCwzMjkuOTR6IiBmaWxsPSIjNEQ0RDREIi8+PC9zdmc+';
	} else {
		$file = LEGULL_URL . "asset/icon-{$size}.png";
	}

	return $file;
}

function legull_generate_terms_to_import() {
	global $shortcode_tags;
	$status          = false;
	$tagnames        = array_keys( $shortcode_tags );
	$tagregexp       = join( '|', array_map( 'preg_quote', $tagnames ) );
	$shortcode_regex = get_shortcode_regex();
	$docs            = apply_filters( 'legull_generate_terms_to_import/list', glob( LEGULL_PATH . "docs/*.md" ) );
	include_once( LEGULL_PATH . 'lib/parsedown.php' );
	$Parsedown = new Parsedown();
	foreach ( $docs as $filename ) {
		$import_file     = basename( $filename );
		$content         = file_get_contents( $filename );
		$check_if_exists = new WP_Query( array( 'meta_key' => 'legull_file', 'meta_value' => $import_file, 'post_type' => LEGULL_CPT ) );

		$import_post = array(
			'post_type'      => LEGULL_CPT,
			'post_status'    => 'publish',
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
		);
		if ( count( $check_if_exists->posts ) ) {
			$import_post['ID'] = $check_if_exists->posts[0]->ID;
		}

		// setup defaults
		$post_title = $import_file;

		$look_for_shortcode = 'legull_part';
		if ( has_shortcode( $content, $look_for_shortcode ) ) {
			$legull_part_regex_pattern = str_replace( $tagregexp, $look_for_shortcode, $shortcode_regex );
			$content                   = preg_replace_callback( '/' . $legull_part_regex_pattern . '/s', 'legull_shortcode_part_include', $content );
		}

		$look_for_shortcode = 'legull_condition';
		if ( has_shortcode( $content, $look_for_shortcode ) ) {
			$legull_part_regex_pattern = str_replace( $tagregexp, $look_for_shortcode, $shortcode_regex );
			$content                   = preg_replace_callback( '/' . $legull_part_regex_pattern . '/s', 'legull_shortcode_condition', $content );
		}

		$look_for_shortcode = 'legull_var';
		if ( has_shortcode( $content, $look_for_shortcode ) ) {
			$legull_var_regex_pattern = str_replace( $tagregexp, $look_for_shortcode, $shortcode_regex );
			preg_match_all( '/' . $legull_var_regex_pattern . '/s', $content, $matches );

			// include space because attributes are not trimmed
			// set page title/name
			if ( in_array( ' name="title"', $matches[3] ) ) {
				$post_title = $matches[5][0];
			}

			// clean the content from [legull_var]
			$content = legull_strip_shortcode( $content, 'legull_var' );
		}

		$import_post['post_title']   = $post_title;
		$import_post['post_name']    = $post_title;
		$import_post['post_content'] = $Parsedown->text( $content );
		if( !empty( $import_post['post_title'] ) && !empty( $import_post['post_content'] ) ) {
			$document_id                 = wp_insert_post( $import_post );
			update_post_meta( $document_id, 'legull_file', $import_file );
			$status = true;
		} else if( !empty( $import_post['ID'] ) ) {
			wp_trash_post( $import_post['ID'] );
		}
	}

	return $status;
}

function legull_shortcode_part_include( $matches ) {
	// $matches = apply_filters( 'legull_shortcode_part_include/matches', $matches );
	$content = '';
	if ( !empty( $matches[5] ) ) {
		$part_path = LEGULL_PATH . "docs/part";
		$file      = apply_filters( 'legull_shortcode_part_include/file', $part_path . '/' . $matches[5] . '.md', $part_path, $matches[5] . '.md' );
		if ( !empty( $file ) && file_exists( $file ) ) {
			$content = file_get_contents( $file );
		}
	}

	return apply_filters( 'legull_shortcode_part_include/content', $content );
}

function legull_shortcode_condition( $matches ) {
	// $matches = apply_filters( 'legull_shortcode_part_include/matches', $matches );
	$content = '';
	if ( preg_match( "/(.*)=[\"|'](.*)[\"|']/", $matches[3], $condition ) && $content = $matches[5] ) {
		$condition_value = legull_get_var( trim( $condition[2] ) );
		if (
			( trim( $condition[1] ) == 'is' && $condition_value ) ||
			( trim( $condition[1] ) == 'isnot' && !$condition_value )
		) {
			$content = $matches[5];
		} else {
			$content = '';
		}
	}

	return apply_filters( 'legull_shortcode_condition/content', $content );
}

function legull_seek_option( $haystack, $needle ) {
	$output = '';
	foreach ( (array) $haystack as $key => $value ) {
		if ( $key == $needle ) {
			$output = $value;
		} elseif ( is_array( $value ) ) {
			$output = legull_seek_option( $value, $needle );
		}
	}

	return $output;
}

function legull_get_value( $field_id, $section = null ) {
	$response = null;
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		$options  = get_option( 'Legull' );
		$response = legull_seek_option( $options, $field_id );
	} else {
		global $legull;
		if ( $section == null ) {
			$response = $legull->getValue( $field_id );
		} else {
			$response = $legull->getValue( $section, $field_id );
		}
	}

	return $response;
}

function legull_get_var( $field_id ) {
	$value = null;
	switch ( $field_id ) {
		case 'last_updated':
			// Todo figure out why date isn't processing
			$value = date( 'F jS, Y', strtotime( legull_get_value( $field_id, 'misc' ) ) );
			break;
		case 'siteurl':
			$link  = legull_get_value( $field_id, 'ownership' );
			$value = sprintf( '<a href="%s">%s</a>', $link, $link );
			break;
		case 'owner_name':
		case 'owner_email':
		case 'owner_locality':
		case 'entity_type':
			$value = legull_get_value( $field_id, 'ownership' );
			break;
		case 'has_DMCA_agent':
			$boolean = legull_get_value( $field_id, 'usercontent' );
			$value   = $boolean == 1 ? true : false;
			break;
		case 'has_over18':
		case 'has_arbitration':
		case 'has_SSL':
			$boolean = legull_get_value( $field_id, 'misc' );
			$value   = $boolean == 1 ? true : false;
			break;
	}

	return $value;
}

function legull_shortcode( $atts, $content = null ) {
	$a = shortcode_atts(
		array(
			'display' => ''
		), $atts
	);

	return legull_get_var( $a['display'] );
}

function legull_shortcode_fake() {
	return '';
}

function legull_strip_shortcode( $content, $shortcode ) {
	global $shortcode_tags;

	$stack          = $shortcode_tags;
	$shortcode_tags = array( $shortcode => 1 );
	$content        = strip_shortcodes( $content );
	$shortcode_tags = $stack;

	return $content;
}
