<?php

declare(strict_types=1);

namespace ApexSEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts( string $hook ): void {
		$screen = get_current_screen();

		// Enqueue on post edit screens and our settings page
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook && strpos( $hook, 'apex-seo' ) === false ) {
			return;
		}

		$plugin_url = plugin_dir_url( dirname( __DIR__, 2 ) . '/apex-seo.php' );

		wp_enqueue_style(
			'apex-seo-admin',
			$plugin_url . 'assets/css/admin.css',
			[],
			'1.0.0'
		);

		wp_enqueue_script(
			'apex-seo-analysis',
			$plugin_url . 'assets/js/analysis.js',
			[ 'jquery', 'wp-data', 'wp-editor' ],
			'1.0.0',
			true
		);
	}
}
