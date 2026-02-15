<?php

declare(strict_types=1);

namespace ApexSEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_admin_menu(): void {
		add_options_page(
			__( 'ApexSEO Settings', 'apex-seo' ),
			__( 'ApexSEO', 'apex-seo' ),
			'manage_options',
			'apex-seo',
			[ $this, 'render_settings_page' ]
		);
	}

	public function register_settings(): void {
		register_setting( 'apex_seo_group', 'apex_seo_settings' );

		add_settings_section(
			'apex_seo_knowledge_graph',
			__( 'Knowledge Graph Settings', 'apex-seo' ),
			null,
			'apex-seo'
		);

		add_settings_field(
			'knowledge_graph_type',
			__( 'Organization or Person?', 'apex-seo' ),
			[ $this, 'render_field_type' ],
			'apex-seo',
			'apex_seo_knowledge_graph'
		);

		add_settings_field(
			'knowledge_graph_name',
			__( 'Name', 'apex-seo' ),
			[ $this, 'render_field_name' ],
			'apex-seo',
			'apex_seo_knowledge_graph'
		);

		add_settings_field(
			'knowledge_graph_logo',
			__( 'Logo URL', 'apex-seo' ),
			[ $this, 'render_field_logo' ],
			'apex-seo',
			'apex_seo_knowledge_graph'
		);
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php _e( 'ApexSEO Settings', 'apex-seo' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'apex_seo_group' );
				do_settings_sections( 'apex-seo' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function render_field_type(): void {
		$options = get_option( 'apex_seo_settings', [] );
		$value   = $options['knowledge_graph_type'] ?? 'Organization';
		?>
		<select name="apex_seo_settings[knowledge_graph_type]">
			<option value="Organization" <?php selected( $value, 'Organization' ); ?>><?php _e( 'Organization', 'apex-seo' ); ?></option>
			<option value="Person" <?php selected( $value, 'Person' ); ?>><?php _e( 'Person', 'apex-seo' ); ?></option>
		</select>
		<?php
	}

	public function render_field_name(): void {
		$options = get_option( 'apex_seo_settings', [] );
		$value   = $options['knowledge_graph_name'] ?? get_bloginfo( 'name' );
		?>
		<input type="text" name="apex_seo_settings[knowledge_graph_name]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}

	public function render_field_logo(): void {
		$options = get_option( 'apex_seo_settings', [] );
		$value   = $options['knowledge_graph_logo'] ?? '';
		?>
		<input type="url" name="apex_seo_settings[knowledge_graph_logo]" value="<?php echo esc_url( $value ); ?>" class="regular-text" />
		<p class="description"><?php _e( 'Paste the full URL of your logo image.', 'apex-seo' ); ?></p>
		<?php
	}
}
