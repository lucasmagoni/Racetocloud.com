<?php

/**
 * The AJAX functionality of the plugin.
 */
class Swiss_Simple_Consent_Ajax {

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
	 * Log the user consent.
	 */
	public function log_consent() {
		check_ajax_referer( 'swiss_consent_nonce', 'nonce' );

		if ( ! isset( $_POST['consent_level'] ) ) {
			wp_send_json_error( 'Missing consent level' );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'swiss_consent_logs';

		$consent_level = sanitize_text_field( $_POST['consent_level'] );
		$ip_address = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip_address = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip_address = $_SERVER['REMOTE_ADDR'];
		}

		$ip_hash = hash( 'sha256', $ip_address );
		$timestamp = current_time( 'mysql' );

		$wpdb->insert(
			$table_name,
			array(
				'ip_hash'       => $ip_hash,
				'consent_level' => $consent_level,
				'timestamp'     => $timestamp,
			),
			array(
				'%s',
				'%s',
				'%s',
			)
		);

		wp_send_json_success();
	}

}
