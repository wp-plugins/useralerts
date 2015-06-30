<?php

/**
 * Uninstallation script
 * Delete all alerts
 */

// If uninstall is not called from WordPress, exit
if (!defined( 'WP_UNINSTALL_PLUGIN')) {
    exit();
}


$args = array(
	'author' => '',
	'posts_per_page'   => -1,
	'orderby'          => 'date',
	'order'            => 'DESC',
	'post_type'        => 'wpd_alert',
	'post_status'      => 'publish',
	'suppress_filters' => true
);
$alerts = get_posts($args);

foreach($alerts as $alert) {
	wp_delete_post($alert->ID, true);
}