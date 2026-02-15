<?php

declare(strict_types=1);

namespace ApexSEO\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Head {

	public function __construct() {
		add_action( 'wp_head', [ $this, 'output_meta_tags' ], 1 );
		add_filter( 'document_title_parts', [ $this, 'filter_title' ] );
		remove_action( 'wp_head', 'rel_canonical' ); // Remove default WP canonical
	}

	public function filter_title( array $title_parts ): array {
		if ( ! is_singular() ) {
			return $title_parts;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $title_parts;
		}

		$seo_title = get_post_meta( $post_id, '_apex_seo_title', true );

		if ( ! empty( $seo_title ) ) {
			// Basic variable replacement
			$replaced_title = $this->replace_variables( $seo_title, $title_parts );
			return [ 'title' => $replaced_title ];
		}

		return $title_parts;
	}

	public function output_meta_tags(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		// Description
		$description = get_post_meta( $post_id, '_apex_seo_description', true );
		if ( ! empty( $description ) ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
		}

		// Robots
		$noindex  = get_post_meta( $post_id, '_apex_seo_noindex', true );
		$nofollow = get_post_meta( $post_id, '_apex_seo_nofollow', true );
		$robots   = [];

		if ( $noindex === '1' ) {
			$robots[] = 'noindex';
		}
		if ( $nofollow === '1' ) {
			$robots[] = 'nofollow';
		}

		if ( ! empty( $robots ) ) {
			echo '<meta name="robots" content="' . esc_attr( implode( ',', $robots ) ) . '" />' . "\n";
		}

		// Canonical
		$custom_canonical = get_post_meta( $post_id, '_apex_seo_canonical', true );
		$canonical_url    = ! empty( $custom_canonical ) ? $custom_canonical : get_permalink( $post_id );

		if ( $canonical_url ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical_url ) . '" />' . "\n";
		}
	}

	private function replace_variables( string $text, array $title_parts ): string {
		$title = isset($title_parts['title']) ? $title_parts['title'] : get_the_title();
		$sep = isset($title_parts['tagline']) ? '-' : (isset($title_parts['site']) ? '-' : '|'); // Fallback separator
		// WP 6.1+ document_title_parts usually has 'title', 'page', 'tagline', 'site'

		if ( empty( $title ) ) {
			$title = get_the_title();
		}

		$replacements = [
			'%%title%%'    => $title,
			'%%sep%%'      => '-', // Ideally get from WP option or custom setting
			'%%sitename%%' => get_bloginfo( 'name' ),
		];

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $text );
	}
}
