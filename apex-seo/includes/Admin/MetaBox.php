<?php

declare(strict_types=1);

namespace ApexSEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MetaBox {

	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_meta_box_data' ] );
	}

	public function add_meta_box(): void {
		$screens = [ 'post', 'page' ];
		// Add to custom post types if needed

		foreach ( $screens as $screen ) {
			add_meta_box(
				'apex_seo_box',
				__( 'ApexSEO Settings', 'apex-seo' ),
				[ $this, 'render_meta_box' ],
				$screen,
				'normal',
				'high'
			);
		}
	}

	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'apex_seo_save_meta_box_data', 'apex_seo_meta_box_nonce' );

		$title         = get_post_meta( $post->ID, '_apex_seo_title', true );
		$description   = get_post_meta( $post->ID, '_apex_seo_description', true );
		$canonical     = get_post_meta( $post->ID, '_apex_seo_canonical', true );
		$focus_keyword = get_post_meta( $post->ID, '_apex_seo_focus_keyword', true );
		$noindex       = get_post_meta( $post->ID, '_apex_seo_noindex', true );
		$nofollow      = get_post_meta( $post->ID, '_apex_seo_nofollow', true );

		?>
		<div class="apex-seo-metabox">
			<p>
				<label for="apex_seo_title"><?php _e( 'SEO Title', 'apex-seo' ); ?></label>
				<input type="text" id="apex_seo_title" name="apex_seo_title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
				<small><?php _e( 'Use %%title%%, %%sep%%, %%sitename%% variables.', 'apex-seo' ); ?></small>
			</p>
			<p>
				<label for="apex_seo_description"><?php _e( 'Meta Description', 'apex-seo' ); ?></label>
				<textarea id="apex_seo_description" name="apex_seo_description" class="widefat" rows="3"><?php echo esc_textarea( $description ); ?></textarea>
				<span id="apex-seo-desc-counter"></span>
			</p>
			<p>
				<label for="apex_seo_focus_keyword"><?php _e( 'Focus Keyword', 'apex-seo' ); ?></label>
				<input type="text" id="apex_seo_focus_keyword" name="apex_seo_focus_keyword" value="<?php echo esc_attr( $focus_keyword ); ?>" class="widefat" />
			</p>
			<p>
				<label for="apex_seo_canonical"><?php _e( 'Canonical URL', 'apex-seo' ); ?></label>
				<input type="url" id="apex_seo_canonical" name="apex_seo_canonical" value="<?php echo esc_url( $canonical ); ?>" class="widefat" />
			</p>
			<p>
				<label>
					<input type="checkbox" name="apex_seo_noindex" value="1" <?php checked( $noindex, '1' ); ?> />
					<?php _e( 'Noindex this page', 'apex-seo' ); ?>
				</label>
				<br>
				<label>
					<input type="checkbox" name="apex_seo_nofollow" value="1" <?php checked( $nofollow, '1' ); ?> />
					<?php _e( 'Nofollow links on this page', 'apex-seo' ); ?>
				</label>
			</p>
            <div id="apex-seo-analysis-result"></div>
		</div>
		<?php
	}

	public function save_meta_box_data( int $post_id ): void {
		if ( ! isset( $_POST['apex_seo_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['apex_seo_meta_box_nonce'], 'apex_seo_save_meta_box_data' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

        // Check for capability to edit SEO settings (assuming 'edit_post' is sufficient for basic meta,
        // but stricter checks could be added via a role manager).
        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_others_posts' ) ) {
             // In a real scenario, we might want to allow editors but restrict others.
             // For now, relies on 'edit_post' which is standard for metaboxes.
        }

		$fields = [
			'apex_seo_title'         => 'sanitize_text_field',
			'apex_seo_description'   => 'sanitize_textarea_field',
			'apex_seo_focus_keyword' => 'sanitize_text_field',
			'apex_seo_canonical'     => 'esc_url_raw',
		];

		foreach ( $fields as $field => $sanitizer ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitizer, $_POST[ $field ] );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}

		// Checkboxes
		$checkboxes = [ 'apex_seo_noindex', 'apex_seo_nofollow' ];
		foreach ( $checkboxes as $checkbox ) {
			$value = isset( $_POST[ $checkbox ] ) ? '1' : '0';
			update_post_meta( $post_id, '_' . $checkbox, $value );
		}
	}
}
