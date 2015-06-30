<?php


class Wp_Digest_Load {

	private $email_alert;
	private $cron_task;
	private $listing;

	function __construct() {
		/* Set the constants needed by the plugin. */
		add_action('plugins_loaded', array(&$this, 'constants'), 1);

		/* Load the widget files. */
		add_action('plugins_loaded', array(&$this, 'includes'), 2);

		/* Create the widget */
		add_action('plugins_loaded', array(&$this, 'widget'), 3);

		/* Create the post type for cron task. */
		add_action('plugins_loaded', array(&$this, 'cron_task'), 4);

		/* Create the listing shortcode. */
		add_action('plugins_loaded', array(&$this, 'listing_shortcode'), 5);

		/* Create the post type for alerts. */
		add_action('init', array(&$this, 'post_type'), 1);

		/* Exec when desactivating */
		register_deactivation_hook(__FILE__, array(&$this, 'desactivation'));

	}

	/**
	 * Defines constants used by the plugin.	
	 */
	public function constants() {
		/* Set debug version. */
		define('WP_DIGEST_DEBUG', WP_DEBUG);

		/* Set the version number of the plugin. */
		define('WP_DIGEST_VERSION', '1.0');

		/* Set constant path to the email alerts plugin directory. */
		define('WP_DIGEST_DIR', trailingslashit(plugin_dir_path(__FILE__ )));

		/* Set constant path to the members plugin URL. */
		define('WP_DIGEST_URI', trailingslashit(plugin_dir_url( __FILE__ )));

		/* Set the constant path to the email alerts includes directory. */
		define('WP_DIGEST_INCLUDES', WP_DIGEST_DIR.trailingslashit('includes'));

		/* Set the constant path to the email alerts assets directory. */
		define('WP_DIGEST_ASSETS', WP_DIGEST_URI.'assets');
	}

	/**
	 * Create the widget plugin
	 */
	public function includes() {
		/* Load libs. */
		require_once(WP_DIGEST_INCLUDES.'lib/class.DJVirtualPage.php');

		/* Load the plugin alert file. */
		require_once(WP_DIGEST_INCLUDES.'alert.php');

		/* Load the plugin cron file. */
		require_once(WP_DIGEST_INCLUDES.'cron.php');

		/* Load the plugin alert listing file. */
		require_once(WP_DIGEST_INCLUDES.'listing.php');
		
		require_once( UA_DIR . '/templates/template-functions.php' ); 

	}

	public function widget() {
		/* Load the widget alert file. */
		require_once(WP_DIGEST_INCLUDES.'widget.php');
	}

	/**
	 * Create the post-type for alerts
	 */
	public function post_type() {
		$this->email_alert = new Wp_Digest_Alert();
		$this->email_alert->create_post_type();
	}

	/**
	 * Create the cron task for alerts
	 */
	public function cron_task() {
		$this->cron_task = new Wp_Digest_Cron();
		$this->cron_task->create_cron_task();
	}

	/**
	 * Create the cron task for alerts
	 */
	public function listing_shortcode() {
		$this->listing = new Wp_Digest_Listing();
	}

	/**
	 * Runs when plugin is desactivated
	 */
	public function desactivation() {
		$this->cron_task->delete_cron_task();
	}

}

$wp_digest_load = new Wp_Digest_Load();

?>