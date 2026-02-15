<?php
/**
 * Plugin Name: Swiss Simple Consent
 * Plugin URI:  https://example.com/swiss-simple-consent
 * Description: A lightweight, GDPR & Swiss nFADP compliant Cookie Consent plugin.
 * Version:     1.0.0
 * Author:      Jules
 * Author URI:  https://example.com
 * License:     GPL-2.0+
 * Text Domain: swiss-simple-consent
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main Class
 */
class Swiss_Simple_Consent {

	/**
	 * Unique identifier for your plugin.
	 *
	 * @var      string
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var      string
	 */
	protected $version;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->plugin_name = 'swiss-simple-consent';
		$this->version     = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function set_locale() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'swiss-simple-consent',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-frontend.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ajax.php';
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Swiss_Simple_Consent_Admin( $this->get_plugin_name(), $this->get_version() );
		// Hooks will be added in the Admin class
		add_action( 'admin_menu', array( $plugin_admin, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $plugin_admin, 'register_settings' ) );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 */
	private function define_public_hooks() {
		$plugin_public = new Swiss_Simple_Consent_Frontend( $this->get_plugin_name(), $this->get_version() );
		// Hooks will be added in the Frontend class
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_scripts' ) );
		add_action( 'wp_head', array( $plugin_public, 'inject_scripts' ) ); // Or wp_footer, depending on needs

		// AJAX Handling
		$plugin_ajax = new Swiss_Simple_Consent_Ajax( $this->get_plugin_name(), $this->get_version() );
		add_action( 'wp_ajax_swiss_consent_log', array( $plugin_ajax, 'log_consent' ) );
		add_action( 'wp_ajax_nopriv_swiss_consent_log', array( $plugin_ajax, 'log_consent' ) );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The current version of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

/**
 * The code that runs during plugin activation.
 */
function activate_swiss_simple_consent() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	Swiss_Simple_Consent_Activator::activate();
}

/**
 * Begins execution of the plugin.
 */
function run_swiss_simple_consent() {
	$plugin = new Swiss_Simple_Consent();
}

register_activation_hook( __FILE__, 'activate_swiss_simple_consent' );
run_swiss_simple_consent();
