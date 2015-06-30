<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 *
 */

function wdxxx_optionsframework_option_name() {

	// This gets the theme name from the stylesheet (lowercase and without spaces)
	$themename = get_option( 'stylesheet' );
	$themename = preg_replace("/\W/", "_", strtolower($themename) );

	$optionsframework_settings = get_option('optionsframework');
	$optionsframework_settings['id'] = $themename;
	update_option('optionsframework', $optionsframework_settings);

	// echo $themename;
}

// http://brassblogs.com/code-snippets/get-page-by-slug
function wdxxx_get_ID_by_slug($page_slug) {
    $page = get_page_by_path($page_slug);
    if ($page) {
        return $page->ID;
    } else {
        return null;
    }
}

add_filter( 'optionsframework_menu', function( $menu ) {
 	$menu['page_title'] = 'UserAlerts';
	$menu['menu_title'] = 'UserAlerts';
	$menu['capability'] = 'edit_theme_options';
	$menu['menu_slug'] = 'useralerts';
	$menu['parent_slug'] = 'options-general.php';
	return $menu;
});

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 */
add_filter( 'of_options', function($options) {


	// Pull all the pages into an array
	$wdxxx_options_pages = array();
	$wdxxx_options_pages_obj = get_pages( 'sort_column=post_parent,menu_order' );
	$wdxxx_options_pages[''] = 'Select a page:';
	foreach ($wdxxx_options_pages_obj as $page) {
		$wdxxx_options_pages[$page->ID] = $page->post_title;
	}



	$options = array();

	$options[] = array(
		'name' => __( 'Sender Settings', 'theme-textdomain' ),
		'type' => 'heading'
	);

	$options[] = array(
		'name' => __( 'Name', 'theme-textdomain' ),
		'desc' => __( "The value of this field  can be anything. Generally, you'll want to use the name of your site." ),		
		'id' => 'useralerts_from_name',
		'type' => 'text',
		'std' => get_bloginfo( 'name' )
		
	);

	$options[] = array(
		'name' => __( 'E-mail', 'theme-textdomain' ),
		'desc' => __( "If you don't want to use your real address, you can enter something like nopreply@yourdomain.com"),			
		'id' => 'useralerts_from_email',
		'type' => 'text',
		'std' => get_bloginfo( 'admin_email' )
		
	);
	


	return $options;
});