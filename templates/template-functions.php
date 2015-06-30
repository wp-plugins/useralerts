<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit; 

function get_mail_template( $slug, $name = null ) {

	$templates = array();
	$name = (string) $name;
	if ( '' !== $name )
		$templates[] = "{$slug}-{$name}.php";

	$templates[] = "{$slug}.php";

	// No file found yet
	$located            = '';
	$template_locations = array(
		get_stylesheet_directory() . '/mail/',
		get_template_directory() . '/mail/',
		UA_DIR . 'templates/mail/'
	);

	// Try to find a template file
	foreach ( (array) $templates as $template ) {

		if ( empty( $template ) ) {
			continue;
		}
		$template  = ltrim( $template, '/' );

		foreach ( (array) $template_locations as $template_location ) {

			if ( empty( $template_location ) ) {
				continue;
			}

			if ( file_exists( trailingslashit( $template_location ) . $template ) ) {
				$located = trailingslashit( $template_location ) . $template;
				break 2;
			}
		}
	}
	if ( !empty( $located ) ) {
		load_template( $located, true );
	}
} 
