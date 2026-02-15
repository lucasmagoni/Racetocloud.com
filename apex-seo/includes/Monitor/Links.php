<?php

declare(strict_types=1);

namespace ApexSEO\Monitor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Links {

	public function __construct() {
		add_action( 'template_redirect', [ $this, 'monitor_404' ] );
	}

	public function monitor_404(): void {
		if ( ! is_404() ) {
			return;
		}

		// Server Load Check
		if ( function_exists( 'sys_getloadavg' ) ) {
			$load = sys_getloadavg();
			if ( is_array( $load ) && $load[0] > 5.0 ) { // Threshold
				return;
			}
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'apex_seo_404s';

		// Setup URL
		$url = $_SERVER['REQUEST_URI'];
		$url = esc_url_raw( $url );

		// Check for existing redirect
		// This should be cached ideally, but direct DB query for now
		$redirect = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE url = %s", $url ) );

		if ( $redirect && ! empty( $redirect->redirect_to ) ) {
			wp_redirect( $redirect->redirect_to, 301 );
			exit;
		}

		// Log 404
		if ( $redirect ) {
			$wpdb->update(
				$table_name,
				[
					'hits'     => $redirect->hits + 1,
					'last_hit' => current_time( 'mysql' ),
				],
				[ 'id' => $redirect->id ]
			);
		} else {
			$wpdb->insert(
				$table_name,
				[
					'url'      => $url,
					'hits'     => 1,
					'last_hit' => current_time( 'mysql' ),
				]
			);
		}
	}

	public static function install(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . 'apex_seo_404s';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			url varchar(255) NOT NULL,
			hits int(11) DEFAULT 0,
			redirect_to varchar(255) DEFAULT '',
			last_hit datetime DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY url (url)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
