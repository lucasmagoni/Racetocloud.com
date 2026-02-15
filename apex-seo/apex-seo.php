<?php
/**
 * Plugin Name: ApexSEO
 * Plugin URI: https://example.com/apex-seo
 * Description: A state-of-the-art SEO plugin for WordPress.
 * Version: 1.0.0
 * Author: ApexSEO Team
 * Author URI: https://example.com
 * Text Domain: apex-seo
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

declare(strict_types=1);

namespace ApexSEO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Autoloader
spl_autoload_register( function ( string $class ) {
	$prefix = 'ApexSEO\\';
	$base_dir = plugin_dir_path( __FILE__ ) . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

// Initialize the plugin
function init(): void {
	if ( class_exists( 'ApexSEO\\Core\\Plugin' ) ) {
		Core\Plugin::get_instance();
	}
}

add_action( 'plugins_loaded', 'ApexSEO\\init' );

register_activation_hook( __FILE__, [ 'ApexSEO\\Core\\Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'ApexSEO\\Core\\Plugin', 'deactivate' ] );
