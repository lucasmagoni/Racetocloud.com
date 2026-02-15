<?php

declare(strict_types=1);

namespace ApexSEO\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Controller {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_meta_fields' ] );
	}

	public function register_meta_fields(): void {
		$fields = [
			'_apex_seo_title',
			'_apex_seo_description',
			'_apex_seo_focus_keyword',
			'_apex_seo_canonical',
			'_apex_seo_noindex',
			'_apex_seo_nofollow',
		];

		foreach ( $fields as $field ) {
			register_meta( 'post', $field, [
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
				// auth_callback handles permission to edit. Read is public via REST if show_in_rest is true.
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				}
			] );
		}
	}
}
