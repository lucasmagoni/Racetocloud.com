<?php

declare(strict_types=1);

namespace ApexSEO\Sitemap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Generator {

	public function __construct() {
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		add_action( 'template_redirect', [ $this, 'render_sitemap' ] );
	}

	public function add_rewrite_rules(): void {
		add_rewrite_rule( '^sitemap_index\.xml$', 'index.php?sitemap_index=1', 'top' );
	}

	public function add_query_vars( array $vars ): array {
		$vars[] = 'sitemap_index';
		return $vars;
	}

	public function render_sitemap(): void {
		if ( get_query_var( 'sitemap_index' ) !== '1' ) {
			return;
		}

		$sitemap = get_transient( 'apex_seo_sitemap' );

		if ( false === $sitemap ) {
			$sitemap = $this->generate_sitemap();
			set_transient( 'apex_seo_sitemap', $sitemap, 12 * HOUR_IN_SECONDS );
		}

		header( 'Content-Type: application/xml; charset=utf-8' );
		echo $sitemap;
		exit;
	}

	private function generate_sitemap(): string {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		$post_types = [ 'post', 'page' ]; // Make dynamic later if needed

		$query = new \WP_Query( [
			'post_type'      => $post_types,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
            // Simple logic: exclude noindex
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => '_apex_seo_noindex',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => '_apex_seo_noindex',
                    'value'   => '1',
                    'compare' => '!=',
                ],
            ],
		] );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$permalink = get_permalink();
				$modified  = get_the_modified_date( 'c' );

				$xml .= '<url>';
				$xml .= '<loc>' . esc_url( $permalink ) . '</loc>';
				$xml .= '<lastmod>' . esc_html( $modified ) . '</lastmod>';
				$xml .= '</url>';
			}
		}

		wp_reset_postdata();

		$xml .= '</urlset>';

		return $xml;
	}
}
