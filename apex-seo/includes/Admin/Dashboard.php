<?php

declare(strict_types=1);

namespace ApexSEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

	public function __construct() {
		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
	}

	public function add_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'apex_seo_dashboard_widget',
			__( 'ApexSEO Overview', 'apex-seo' ),
			[ $this, 'render_dashboard_widget' ]
		);
	}

	public function render_dashboard_widget(): void {
		$posts_without_desc = $this->count_posts_without_meta( '_apex_seo_description' );
		$posts_without_kw   = $this->count_posts_without_meta( '_apex_seo_focus_keyword' );

		echo '<div class="apex-seo-dashboard">';
		echo '<p>' . sprintf( __( 'Posts without Meta Description: <strong>%d</strong>', 'apex-seo' ), $posts_without_desc ) . '</p>';
		echo '<p>' . sprintf( __( 'Posts without Focus Keyword: <strong>%d</strong>', 'apex-seo' ), $posts_without_kw ) . '</p>';
		echo '</div>';
	}

	private function count_posts_without_meta( string $meta_key ): int {
		$query = new \WP_Query( [
			'post_type'      => [ 'post', 'page' ],
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => [
				'relation' => 'OR',
				[
					'key'     => $meta_key,
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => $meta_key,
					'value'   => '',
					'compare' => '=',
				],
			],
		] );

		return $query->found_posts;
	}
}
