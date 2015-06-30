<?php
/**
 * Manage alerts for the wp digest plugin
 *
 * @package Email-Alerts
 * @subpackage Functions
 */

class Wp_Digest_Alert {

	//Allowed frequencies
	public $digest_frequencies = array(
		'1' => 'Daily',
		'7' => 'Weekly',
		'30' => 'Monthly'
	);

	public $custom_post_type = 'wpd_alert';
	public $max_alerts = 15;

	private $id;
	private $title;
	private $email;
	private $frequency;
	private $last_exec;
	private $query;
	private $unsubscribe_query_vars = array('key', 'email');

	function __construct() {
	}

	/**
	 * Create new alert post type
	 */
	public function create_post_type() {
		register_post_type(
			$this->custom_post_type,
	    	array(
	      		'labels' => array(
		        	'name' => __('Digest alerts', 'wp_digest_domain'),
		        	'singular_name' => __('digest-alert', 'wp_digest_domain')
	      		),
	      		'supports' => 'custom-fields',
		      	'public' => false,
		      	'has_archive' => false,
		      	'exclude_from_search' => true,
		      	'show_ui' => false,
		      	'capability_type' => 'post',
		      	'show_in_menu' => false
    		)
	  	);

	  	//create virtual page for unsubscribe
	    add_action('init', array(&$this, 'generate_unsubscribe_page'));
	}

	/**
	 * Generate the unsubscribe page
	 */
	public function generate_unsubscribe_page() {
		//Check for get vars
		foreach($this->unsubscribe_query_vars as $var) {
			if(!isset($_GET[$var]) || $_GET[$var] == '') {
				return;
			}
		}

		$key = rawurldecode($_GET["key"]);
		$email = rawurldecode($_GET["email"]);

		$alert = $this->get_alert_by_key($key);

		$content = '';
		if($alert && count($alert) == 1) {
			$alert = $alert[0];
			$metas = get_post_meta($alert->ID);
			if($email == $metas["email"][0]) {
				$success = wp_delete_post($alert->ID, true);
 				if(!$success) {
 					$content = 'An error occurred';	
 				} else {
					$content = 'You have been successfully unsubscribed';
 				}
			} else {
				$content = 'Authorization failed';	
			}
		} else {
			$content = 'No alert found';
		}

		//Generate virtual page with library
        $args = array(
        	'slug' => 'unsubscribe',
            'title' => 'Alert subscription',
            'content' => $content
        );
    	$pg = new DJVirtualPage($args);
	}

	/**
	 * Register or update new alert post
	 * @param: array. 2 optional keys : ID and last_exec
	 * @param: boolean. optional to set update or not
	 * @return boolean.
	 */
	public function register_new_alert($params) {
		$this->title = $params["title"];
		$this->email = $params["email"];
		$this->frequency = $params["frequency"];
		$this->query = $params["query"];
		$this->last_exec = time();

		//Register new alert post
		$new_alert = array(
			'ID' => '',
			'post_title' => $this->title,
			'post_type' => $this->custom_post_type,
			'post_status'   => 'publish'
		);
		$new_alert_id = wp_insert_post($new_alert, true);

		//Everything is ok
		if(!is_wp_error($new_alert_id)) {
			//Add meta
			update_post_meta($new_alert_id, 'email', $this->email);
			update_post_meta($new_alert_id, 'frequency', $this->frequency);
			update_post_meta($new_alert_id, 'query', $this->query);
			update_post_meta($new_alert_id, 'last_exec', $this->last_exec);
			update_post_meta($new_alert_id, 'secret_key', wp_generate_password(20, true, true));
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Send digest email
	 * @param alert object
	 * @param new_posts: array of posts objects
	 */
	public function send_alert_email($alert, $new_posts) {

// 	get_mail_template('default');
	include( UA_DIR . 'templates/mail/default.php' ); 

	
	wp_mail($to, $subject, $body, $headers);
	}
	
	


	/**
	 * Send empty email (debugging)
	 * @param email: email address to send
	 * @param new_posts: array of posts objects
	 */
	public function send_alert_empty_email($alert) {
		$metas = get_post_meta($alert->ID);
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$headers[] = 'From: '.get_option('blogname').' <'.get_option('admin_email').'>';
		$to = $metas["email"][0];
	    $subject = $alert->post_title;
	    $body = 'No new posts';
	    $body .= '<p>'.$this->generate_unsubscribe_link($alert).'</p>';
	    wp_mail($to, $subject, $body, $headers);
	}

	/**
	 * Get all alerts
	 * @param: optional. Author ID
	 */
	public function get_all_alerts($user_id = '') {
		$args = array(
			'author' 		   => $user_id,
			'posts_per_page'   => -1,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => $this->custom_post_type,
			'post_status'      => 'publish',
			'suppress_filters' => true
		);
		$alerts = get_posts($args);
		return $alerts;
	}

	/**
	 * Delete all alerts
	 */
	public function delete_all_alerts() {
		$alerts = $this->get_all_alerts();
		foreach($alerts as $alert) {
			wp_delete_post($alert->ID, true);
		}
	}

	/**
	 * Generate a custom unsubscribe link for an alert
	 * @param: alert object
	 */
	private function generate_unsubscribe_link($alert) {
		$metas = get_post_meta($alert->ID);
		$url = get_site_url().'/unsubscribe?key='.rawurlencode($metas["secret_key"][0]).'&email='.rawurlencode($metas["email"][0]);
		$link = '<a href="'.$url.'">Unsubscribe</a>.';
		return $link;
	}

	/**
	 * Get an alert by hash key
	 */
	private function get_alert_by_key($key) {
		$args = array(
		    'meta_query' => array(
		        array(
		            'key' => 'secret_key',
		            'value' => $key
		        )
		    ),
		    'post_type' => $this->custom_post_type,
		    'post_status' => 'publish',
		    'posts_per_page' => -1,
		    'suppress_filters' => true
		);
		$posts = get_posts($args);
		return $posts;
	}

}



?>