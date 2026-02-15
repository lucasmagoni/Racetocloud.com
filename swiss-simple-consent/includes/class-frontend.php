<?php

/**
 * The public-facing functionality of the plugin.
 */
class Swiss_Simple_Consent_Frontend {

	/**
	 * The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		$options = get_option( $this->plugin_name . '_options' );
		$enable_banner = isset( $options['enable_banner'] ) ? $options['enable_banner'] : 0;

		// Always enqueue if enabled, because we need to check cookie in JS too (to show banner or not)
		// Or maybe only if cookie is not set?
		// Better to always load JS to handle the "Settings" button if the user wants to change consent later.

		if ( ! $enable_banner ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/css/style.css', array(), $this->version, 'all' );

		// Custom styles from settings
		$banner_bg = isset( $options['banner_bg_color'] ) ? $options['banner_bg_color'] : '#ffffff';
		$btn_color = isset( $options['button_color'] ) ? $options['button_color'] : '#0073aa';

		$custom_css = "
			#swiss-consent-banner { background-color: {$banner_bg}; }
			#swiss-consent-banner .swiss-consent-btn-primary { background-color: {$btn_color}; border-color: {$btn_color}; }
		";
		wp_add_inline_style( $this->plugin_name, $custom_css );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/js/consent.js', array(), $this->version, true );

		wp_localize_script( $this->plugin_name, 'swiss_consent_obj', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'swiss_consent_nonce' ),
			'texts'    => array(
				'banner_text' => __( 'We use cookies to improve your experience. Some are essential, others help us improve.', 'swiss-simple-consent' ),
				'accept_all'  => __( 'Accept All', 'swiss-simple-consent' ),
				'reject_all'  => __( 'Reject All', 'swiss-simple-consent' ),
				'settings'    => __( 'Settings', 'swiss-simple-consent' ),
				'save'        => __( 'Save Settings', 'swiss-simple-consent' ),
				'marketing'   => __( 'Marketing', 'swiss-simple-consent' ),
				'statistics'  => __( 'Statistics', 'swiss-simple-consent' ),
				'essential'   => __( 'Essential', 'swiss-simple-consent' ),
			)
		) );
	}

	/**
	 * Inject scripts into wp_head based on consent.
	 */
	public function inject_scripts() {
		$options = get_option( $this->plugin_name . '_options' );
		$marketing_scripts = isset( $options['marketing_scripts'] ) ? $options['marketing_scripts'] : '';
		$statistics_scripts = isset( $options['statistics_scripts'] ) ? $options['statistics_scripts'] : '';

		// Check for consent cookie
		$consent_cookie = isset( $_COOKIE['privacy_consent_v1'] ) ? sanitize_text_field( $_COOKIE['privacy_consent_v1'] ) : '';

		// Parse consent
		// Format: "marketing,statistics" or "essential"
		$consents = explode( ',', $consent_cookie );

		// Always output if user has consented
		if ( in_array( 'marketing', $consents ) ) {
			echo "<!-- Swiss Simple Consent: Marketing Scripts -->\n";
			echo $marketing_scripts . "\n";
		}

		if ( in_array( 'statistics', $consents ) ) {
			echo "<!-- Swiss Simple Consent: Statistics Scripts -->\n";
			echo $statistics_scripts . "\n";
		}
	}

}
