<?php

declare(strict_types=1);

namespace ApexSEO\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Manager {

	public function __construct() {
		add_action( 'wp_head', [ $this, 'output_schema' ], 20 );
	}

	public function output_schema(): void {
		if ( ! is_singular() && ! is_front_page() ) {
			return;
		}

		$schema = $this->get_schema_data();

		if ( ! empty( $schema ) ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		}
	}

	private function get_schema_data(): array {
		$schema = [
			'@context' => 'https://schema.org',
			'@graph'   => [],
		];

		// Knowledge Graph (Organization/Person)
		$schema['@graph'][] = $this->get_organization_schema();

		// WebPage / Article / Product
		if ( is_singular() ) {
			$post_schema = $this->get_post_schema();
			if ( $post_schema ) {
				$schema['@graph'][] = $post_schema;
			}
		}

		return $schema;
	}

	private function get_organization_schema(): array {
		$options = get_option( 'apex_seo_settings', [] );
		$type    = $options['knowledge_graph_type'] ?? 'Organization';
		$name    = $options['knowledge_graph_name'] ?? get_bloginfo( 'name' );
		$logo    = $options['knowledge_graph_logo'] ?? '';

		$org_schema = [
			'@type' => $type,
			'@id'   => home_url( '/#organization' ),
			'name'  => $name,
			'url'   => home_url( '/' ),
		];

		if ( ! empty( $logo ) ) {
			$org_schema['logo'] = [
				'@type' => 'ImageObject',
				'url'   => $logo,
			];
		}

		return $org_schema;
	}

	private function get_post_schema(): ?array {
		$post = get_post();
		if ( ! $post ) {
			return null;
		}

		$post_type = get_post_type( $post );
		$schema_type = 'WebPage';

		if ( $post_type === 'post' ) {
			$schema_type = 'Article';
		} elseif ( $post_type === 'product' ) {
			$schema_type = 'Product';
		}

		$canonical = get_post_meta( $post->ID, '_apex_seo_canonical', true ) ?: get_permalink( $post );
		$description = get_post_meta( $post->ID, '_apex_seo_description', true ) ?: get_the_excerpt( $post );

		$data = [
			'@type'       => $schema_type,
			'@id'         => $canonical . '#' . strtolower( $schema_type ),
			'url'         => $canonical,
			'name'        => get_the_title( $post ),
			'description' => $description,
			'isPartOf'    => [ '@id' => home_url( '/#website' ) ],
            'publisher'   => [ '@id' => home_url( '/#organization' ) ],
		];

		if ( $schema_type === 'Article' ) {
			$data['datePublished'] = get_the_date( 'c', $post );
			$data['dateModified']  = get_the_modified_date( 'c', $post );
			$author_id = $post->post_author;
			$data['author'] = [
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $author_id ),
                'url'   => get_author_posts_url( $author_id ),
			];
		}

        // Add Product schema details if needed, for now basic structure
        if ( $post_type === 'product' && function_exists('wc_get_product') ) {
             // Basic placeholder for WooCommerce integration
             $product = wc_get_product( $post->ID );
             if ( $product ) {
                 $data['sku'] = $product->get_sku();
                 $data['offers'] = [
                     '@type' => 'Offer',
                     'price' => $product->get_price(),
                     'priceCurrency' => get_woocommerce_currency(),
                 ];
             }
        }

		return $data;
	}
}
