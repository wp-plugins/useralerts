<?php
/**
 * Manage alert listing via shortcode
 *
 * @package Email-Alerts
 * @subpackage Functions
 */

class Wp_Digest_Listing extends Wp_Digest_Alert {

	function __construct() {
		add_shortcode('wp_digest_listing', array(&$this, 'create_listing_shortcode'));
	}

	public function create_listing_shortcode() {
		wp_enqueue_style(
			'wp_digest_listing_css',
			WP_DIGEST_ASSETS.'/css/listing_css.css'
		);

	 	$current_user_id = get_current_user_id();
	 	$alerts = array();

		//Listing form posting
	 	if(isset($_POST) && !empty($_POST["wp_digest_listing_form"])) {
	 		$form_errors = array();
			$form_post_id = '';
	 		$form_post_array = $_POST["wp_digest_listing_form"];

	 		//check errors
	 		if(!isset($form_post_array["wp_digest_listing_id"]) || trim($form_post_array["wp_digest_listing_id"]) == '') {
	 			$form_errors["id"] = __('An unexpected error occurred.', 'wp_digest_domain');
	 		} else {
	 			$form_post_id = esc_attr(trim($form_post_array["wp_digest_listing_id"]));
	 		}

	 		//No errors
	 		if(empty($form_errors)) {
	 			$post_to_delete = get_post($form_post_id, OBJECT);
	 			//Check if author
	 			if(($post_to_delete && $post_to_delete->post_author == $current_user_id) || WP_DIGEST_DEBUG) {
	 				$success = wp_delete_post($form_post_id, true);
	 				if(!$success) {
	 					$form_errors["error"] = __('An unexpected error occurred.', 'wp_digest_domain');
	 				}
	 			} else {
				 	$form_errors["authorization"] = __('You don\'t have the authorization to do this.', 'wp_digest_domain');
	 			}
	 		}
	 	}

	 	if(WP_DIGEST_DEBUG) {
	 		$current_user_id = '';
	 	}
	 	if($current_user_id != 0 || WP_DIGEST_DEBUG) {
			$alerts = parent::get_all_alerts($current_user_id);
	 	}

		$html = '';
		if(!empty($form_errors)) {
			foreach($form_errors as $error => $message) {
				$html .= '<p class="error">'.$message.'</p>';
			}
		}

		if(!empty($alerts)) {
		$html .= '
		<div class="section group listings-header">
	<div class="col span_6_of_12">
	Title
	</div>
	<div class="col span_3_of_12">
	Created
	</div>
	<div class="col span_2_of_12">
	Interval
	</div>
	<div class="col span_1_of_12">
	
	</div>
	</div>';
			foreach($alerts as $alert) :
					$metas = get_post_meta($alert->ID);

					$html .= '<div class="section group listings-item">';		
					$html .= '<div class="col span_6_of_12">' . $alert->post_title . '</div>';
					$html .= '<div class="col span_3_of_12">'.$alert->post_date.'</div>';
					$html .= '<div class="col span_2_of_12">';

				if ($metas["frequency"][0] == "1") {
					$html .= 'daily';
				} elseif ($metas["frequency"][0] == "7") {
					$html .= 'weekly';		
				} elseif ($metas["frequency"][0] == "30") {
					$html .= 'monthly';
				} else {
					$html .= 'seconds';
				}
	
					$html .= '</div>';
					$html .= '<div class="col span_1_of_12"><form name="wp_digest_listing_form" class="wp_digest_listing_form" method="POST" >';
						$html .= '<input type="hidden" name="wp_digest_listing_form[wp_digest_listing_id]" value="'.$alert->ID.'" />';
						$html .= '<input class="wp_digest_listing_right" type="submit" value="'.__('X', 'wp_digest_domain').'" />';
					$html .= '</form></div>';
					$html .= '</div> <!-- END SECTION GROUP -->';
					if(WP_DIGEST_DEBUG) {
						$html .= '<p>'.$metas["query"][0].'</p>';
					}
			endforeach;
		} else {
			if($current_user_id == 0) {
				$html .= '<p>You need to login to manage your alerts.</p>';
			} else {
				$html .= '<p>You have no alerts registered.</p>';
			}
		}
		return $html;
	}

}