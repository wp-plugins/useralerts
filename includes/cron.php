<?php
/**
 * Manage cron tasks for the wp digest plugin
 *
 * @package Email-Alerts
 * @subpackage Functions
 */

class Wp_Digest_Cron extends Wp_Digest_Alert {

	private $cron_name = 'wpd_cron_alert';

	function __construct() {
		add_action($this->cron_name, array(&$this, 'execute_cron_task'));
	}

	/*
	 * Register cron task
	 */
	public function create_cron_task() {
		if(!wp_next_scheduled($this->cron_name)) {
			//Lowest email frequency is daily, so run cron daily
	   		wp_schedule_event(time(), 'daily', $this->cron_name);
		}
	}

	/**
	 * Execute cron digest mail
	 */
	public function execute_cron_task() {
		global $wpdb;

		$alerts = parent::get_all_alerts();
		foreach($alerts as $alert) :
			$metas = get_post_meta($alert->ID);
			$alert_frequency = $metas["frequency"][0];
			$alert_last_exec = $metas["last_exec"][0];
			$alert_email = $metas["email"][0];
			$alert_query = $metas["query"][0];

			//Check if current time > last exec time + frequency in seconds
			if(time() > ($alert_last_exec + ($alert_frequency * 86400))) {
				//we need to check if new posts of the query
			 	$query_posts = $wpdb->get_results($alert_query, OBJECT);

			 	//New added posts from query
			 	$new_posts = array();

		 		//Check if inside those posts there are some newer since last alert
			 	foreach($query_posts as $query_post) {
			 		$post = get_post($query_post->ID);
			 		if(get_post_time('U', true, $query_post->ID) >= $alert_last_exec) {
			 			$new_posts[] = $post;
			 		}
			 	}

			 	//Send digest email
			 	$wp_digest_alert = new Wp_Digest_Alert();
			 	
			 	$wpd_count = wp_count_posts( 'wpd_alert' );

			 	if(!empty($new_posts) && ($wpd_count->publish < 100) ) {
					//Send email
					$wp_digest_alert->send_alert_email($alert, $new_posts);
			 	} else {
			 		if((WP_DIGEST_DEBUG) && ($wpd_count->publish < 100) ) {
			 			$wp_digest_alert->send_alert_empty_email($alert);
			 		}
			 	}

				//Set new exec time on alert post
				update_post_meta($alert->ID, 'last_exec', time());
			}
		endforeach;
	}

	/*
	 * Delete cron task
	 */
	public function delete_cron_task() {
		$timestamp = wp_next_scheduled($this->cron_name);
		wp_unschedule_event($timestamp, $this->cron_name);
	}

}

?>