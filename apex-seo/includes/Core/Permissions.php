<?php

declare(strict_types=1);

namespace ApexSEO\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Permissions {

	public static function can_manage_options(): bool {
		return current_user_can( 'manage_options' );
	}

	public static function can_edit_seo_meta( int $post_id ): bool {
		return current_user_can( 'edit_post', $post_id );
	}
}
