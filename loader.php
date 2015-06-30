<?php

/**

 * Plugin Name: UserAlerts (Basic)
 * Description: new post notifications for WordPress
 * Plugin URI: http://useralerts.org
 * Version: 1.0.9
 * License: GPL
 * Text Domain: uax

 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit; 

define ('UA_DIR', plugin_dir_path( __FILE__ ));

// require_once( UA_DIR . '/templates/template-functions.php' ); 

require_once('wp-digest.php');

// require_once( UA_DIR . 'includes/settings/options-framework.php' ); 
require_once( UA_DIR . 'includes/options.php' ); 

require_once( UA_DIR . 'includes/tgm/config.php' ); 

function useralerts_limit_notice() {
	$count_posts = wp_count_posts( 'wpd_alert' );

	if ($count_posts->publish > 100) { 

	$class = "error";
	$message = "UserAlerts has exceeded 100 alerts. All functionality has been disabled. If you wish to continue using this software, please upgrade to <a href='http://userpress.org/alerts/'>UserAlerts Pro</a>.";
        echo "<div class=\"$class\"> <p>$message</p></div>"; 
        
    } elseif ($count_posts->publish > 80) { 

	$class = "update-nag";
	$message = "UserAlerts is about to exceed the 100 alerts limit. If you wish to continue using this software without disruption, please upgrade to <a href='http://userpress.org/alerts/'>UserAlerts Pro</a>.";
        echo "<div class=\"$class\"> <p>$message</p></div>"; 
	}
    
    
}




add_action( 'admin_notices', 'useralerts_limit_notice' ); 
