<?php

declare(strict_types=1);

namespace ApexSEO\Images;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Auto {

	public function __construct() {
		add_action( 'add_attachment', [ $this, 'auto_set_image_meta' ] );
	}

	public function auto_set_image_meta( int $post_id ): void {
		if ( ! wp_attachment_is_image( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		// Use filename as Title (if title is just filename without extension, WP does this by default,
		// but we can enforce or clean it up more).
		// WP default behavior: Title is filename without extension.
		// We can capitalize or replace hyphens with spaces.

		$title = $post->post_title;
		$new_title = ucwords( str_replace( [ '-', '_' ], ' ', $title ) );

		if ( $title !== $new_title ) {
			wp_update_post( [
				'ID'         => $post_id,
				'post_title' => $new_title,
			] );
		}

		// Auto-populate ALT text from Title if missing
		$alt = get_post_meta( $post_id, '_wp_attachment_image_alt', true );

		if ( empty( $alt ) ) {
			update_post_meta( $post_id, '_wp_attachment_image_alt', $new_title );
		}
	}
}
