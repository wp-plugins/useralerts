<?php
/**
 * Create and register the widget for the email alerts plugin
 *
 * @package WP-Digest
 * @subpackage Functions
 */

class Wp_Digest_Widget extends WP_Widget {

	private $query_args = array();
	private $query_fetched = false;
	private $form_errors = array();

	function __construct() {
		add_action('pre_get_posts', array(&$this, 'fetch_query'));
		parent::__construct(
			'wp_digest_widget',
			__('UserAlerts', 'wp_digest_domain'),
			array(
				'description' => __( 'Widget for subscribing to email alerts based on current page', 'wp_digest_domain')
			)
		);
	}

	/**
	 * Fetch main params on first time
	 */
	function fetch_query($query) {
		if((is_home() || $query->is_search() || $query->is_archive()) && !is_admin() && !$this->query_fetched) {
			if(WP_DIGEST_DEBUG) {
				echo '<pre>';
				var_dump($query->query);
				echo '</pre>';
			}
			$this->query_args = $query->query;
			$this->query_fetched = true;
		}
	}

	/**
	 * Widget Front End
	 */
	public function widget($args, $instance) {
	 	global $wp_query;


		//New alert instance
		$email_alert = new Wp_Digest_Alert();

	 	

		//Alert allowed frequencies
		$allowed_frequencies = $email_alert->digest_frequencies;

		if(WP_DIGEST_DEBUG) {
			$allowed_frequencies['0.00001157407'] = 'Second';
		}

		// Load resources
		if(is_active_widget(false, false, $this->id_base)) {
			wp_enqueue_script(
        		'wp_digest_easyModal_js',
        		WP_DIGEST_ASSETS.'/js/jquery.easyModal.js',
        		array('jquery')
    		);
        	wp_enqueue_script(
        		'wp_digest_widget_js',
        		WP_DIGEST_ASSETS.'/js/widget_js.js',
        		array('wp_digest_easyModal_js')
    		);
    		wp_enqueue_style(
    			'wp_digest_widget_css',
    			WP_DIGEST_ASSETS.'/css/widget_css.css'
			);
	 	}

	 	//Widget form posting
	 	if(isset($_POST) && !empty($_POST["wp_digest_widget_form"])) {
	 		$form_post_array = $_POST["wp_digest_widget_form"];
	 		$form_post_email = '';
	 		$form_post_frequency = '';
	 		$form_post_title = '';

	 		//check errors
	 		if(!isset($form_post_array["wp_digest_widget_email_address"]) || trim($form_post_array["wp_digest_widget_email_address"]) == '') {
	 			$this->form_errors["email"] = true;
	 		} else {
	 			$email = esc_attr(trim($form_post_array["wp_digest_widget_email_address"]));
	 			if(!is_email($email)) {
	 				$this->form_errors["email"] = true;
	 			} else {
	 				$form_post_email = $email;
	 			}
	 		}

	 		if(!isset($form_post_array["wp_digest_widget_frequency"]) || trim($form_post_array["wp_digest_widget_frequency"]) == '') {
	 			$this->form_errors["frequency"] = true;
	 		} else {
	 			$frequency = esc_attr(trim($form_post_array["wp_digest_widget_frequency"]));
	 			//frequency not allowed
	 			if(!array_key_exists($frequency, $allowed_frequencies)) {
	 				$this->form_errors["frequency"] = true;
	 			} else {
	 				$form_post_frequency = esc_attr(trim($form_post_array["wp_digest_widget_frequency"]));
	 			}
	 		}

	 		if(!isset($form_post_array["wp_digest_widget_title"]) || trim($form_post_array["wp_digest_widget_title"]) == '') {
	 			$this->form_errors["title"] = true;
	 		} else {
	 			$title = esc_attr(trim($form_post_array["wp_digest_widget_title"]));
	 			$form_post_title = $this->generate_unique_title($title);
	 		}


	 		//fetch only main query. Potential problems...
		 	if(empty($this->form_errors)) {
		 		//Get previous query args
		 		$query_args = $this->query_args;
		 		//Change nb results
		 		$query_args = array_merge($query_args, array('posts_per_page' => $email_alert->max_alerts));
		 		//Rerun query
		 		query_posts($query_args);
		 		$params = array(
	 				"title"		=> $form_post_title,
	 				"email" 	=> $form_post_email,
	 				"frequency" => $form_post_frequency,
	 				"query"		=> $wp_query->request
 				);
 				if($wp_query->request != '') {
	 				$this->register_success = $email_alert->register_new_alert($params);
 				} else {
 					$this->register_success = false;
 				}
		 		wp_reset_query();
	 		}
	 	}


	 	//Display widget
		$title = apply_filters('widget_title', $instance['title']);
		echo $args['before_widget'];
		// if(!empty($title) && !is_singular()) {
		if(!empty($title) && $this->query_fetched) {
			echo $args['before_title'].$title.$args['after_title'];
			?>
			<p>Receive an e-mail when there are new posts matching this query.</p>
				<form method="POST" name="wp_digest_widget_form_dummy" id="wp_digest_widget_form_dummy">
					<?php 
						global $current_user;
      					get_currentuserinfo();
      					$email_value = $current_user->user_email;
					?>
					<input  type="email" id="wp_digest_widget_form_dummy_email" placeholder="<?php _e('Email your address', 'wp_digest_domain') ?>" value="<?php echo $email_value ?>" class="wp_digest_widget_email" required />
			<?php	if(WP_DIGEST_DEBUG) { ?>
						<select id="wp_digest_widget_form_dummy_frequency" class="wp_digest_widget_duration" >
							<?php
								foreach($allowed_frequencies as $frequency => $label) :
									?>
										<option value="<?php echo $frequency ?>"><?php _e($label, 'wp_digest_domain') ?></option>
									<?php
								endforeach;
							?>
						</select>
			<?php } else { ?>

					<input type="hidden" value="1" id="wp_digest_widget_form_dummy_frequency" class="wp_digest_widget_duration" >

			<?php } ?>
					<p><input type="submit" id="wp_digest_widget_modal_open" value="<?php _e('Subscribe', 'wp_digest_domain') ?>"  /></p>
					<?php
						add_action('wp_footer', array(&$this, 'insert_widget_form'));
					?>
				</form>
			<?php
			echo $args['after_widget'];
		}
	}

	/**
	 * Insert widget form in footer (avoid CSS overlay troubles)
	 */
	public function insert_widget_form() {
		?>
	    <div id="wp_digest_widget_modal" class="wp_digest_widget_modal">
	    	<form action="#wp_digest_widget_modal" method="POST" name="wp_digest_widget_form" id="wp_digest_widget_form">
	    		<input type="hidden" name="wp_digest_widget_form[wp_digest_widget_email_address]" id="wp_digest_widget_form_email" required />
				<input type="hidden" name="wp_digest_widget_form[wp_digest_widget_frequency]" id="wp_digest_widget_form_frequency" required />
			    <div class="wp_digest_widget_modal_header">
		      		<a href="javascript:void(0)" id="wp_digest_widget_modal_close" class="wp_digest_widget_modal_close">x</a>
			    </div>
			    <div class="wp_digest_widget_modal_body">
			    	<?php
						//Display form errors message
			    		if(!empty($this->form_errors["email"])) {
							?>
								<p class="error"><?php _e('Invalid email address', 'wp_digest_domain') ?></p>
							<?php
						}
						if(!empty($this->form_errors["frequency"])) {
							?>
								<p class="error"><?php _e('Invalid frequency selection', 'wp_digest_domain') ?></p>
							<?php
						}
						//Display form errors message
			    		if(!empty($this->form_errors["title"])) {
							?>
								<p class="error"><?php _e('Invalid alert title', 'wp_digest_domain') ?></p>
							<?php
						}

						//Display registration message 
						if(isset($this->register_success)) {
							if($this->register_success) {
								?>
									<p class="success"><?php _e('You have been successfully subscribed', 'wp_digest_domain') ?></p>
								<?php
							} else {
								?>
									<p class="error"><?php _e('An unknown error appeared during subscription', 'wp_digest_domain') ?></p>
								<?php
							}
						} else {
							?>
					      		<p>
						      		<label for="wp_digest_widget_form[wp_digest_widget_title]" ><?php _e('Enter a title for this alert', 'wp_digest_domain') ?></p>
								<p> 
									<input type="text" placeholder="Enter your email address" name="wp_digest_widget_form[wp_digest_widget_title]" placeholder="<?php _e('Title', 'wp_digest_domain') ?>" value="Alert #1" id="wp_digest_widget_form[wp_digest_widget_title]" required /> 
								</p>
					      		<input type="submit" value="<?php _e('Save', 'wp_digest_domain') ?>" />
							<?php
						}

						//Reset
						$this->form_errors = array();
						$this->register_success = false;
					?>
			    </div>
		    </form>
	  	</div>
	  	<?php
	}

	/**
	 * Widget Back End
	 */
	public function form($instance) {
		if(isset($instance['title'])) {
			$title = $instance['title'];
		} else {
			$title = __('New title', 'wp_digest_domain');
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<?php 
	}
	
	/**
	 * Updating widget replacing old instance with new
	 */ 
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = !empty($new_instance['title']) ? strip_tags($new_instance['title']) : '';
		return $instance;
	}

	/**
	 * Generate unique title for alert
	 */
	private function generate_unique_title($title) {
		$current_user_id = get_current_user_id();
		$new_title = $title;
		$email_alert = new Wp_Digest_Alert();
		//Get all alerts
		$args = array(
			'author' 		   => $current_user_id,
			'posts_per_page'   => -1,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => $email_alert->custom_post_type,
			'post_status'      => 'publish',
			'suppress_filters' => true
		);
		$alerts = get_posts($args);
		$alert_ids = array();

		if(preg_match('/ #[0-9]+$/', $title)) {
			foreach($alerts as $alert) :
				if(preg_match('/ #[0-9]+$/', $alert->post_title)) {
					$split = explode('#', $alert->post_title);
					$alert_ids[$split[1]] = $split[1];
				}
			endforeach;

			ksort($alert_ids);
			foreach($alerts as $alert) :
				if($alert->post_title === $title) {
					$split = explode('#', $title);
					end($alert_ids);
					$split[1] = key($alert_ids)+1;
					$new_title = implode('#', $split);
				}
			endforeach;
		}
		return $new_title;
	}

}

// Register and load the widget
function wp_digest_load_widget() {
    register_widget('Wp_Digest_Widget');
}
add_action('widgets_init', 'wp_digest_load_widget');

?>