<?php

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options
delete_option( 'apex_seo_settings' );
delete_option( 'apex_seo_version' );
delete_transient( 'apex_seo_sitemap' );

// Delete custom table
global $wpdb;
$table_name = $wpdb->prefix . 'apex_seo_404s';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// Delete post meta (Efficiently)
// Running a direct DELETE query is faster than looping through all posts
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $wpdb->esc_like( '_apex_seo_' ) . '%' ) );
