<?php

/**
 * The admin-specific functionality of the plugin.
 */
class Swiss_Simple_Consent_Admin {

	/**
	 * The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the administration menu.
	 */
	public function add_plugin_admin_menu() {
		add_options_page(
			'Swiss Simple Consent Settings',
			'Swiss Consent',
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_settings_page' )
		);
	}

	/**
	 * Register the settings.
	 */
	public function register_settings() {
		register_setting( $this->plugin_name, $this->plugin_name . '_options', array( $this, 'sanitize_options' ) );

		// Section: General
		add_settings_section(
			'swiss_simple_consent_general',
			'General Settings',
			null,
			$this->plugin_name
		);

		add_settings_field(
			'enable_banner',
			'Enable Banner',
			array( $this, 'field_enable_banner_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_general'
		);

		// Section: Scripts
		add_settings_section(
			'swiss_simple_consent_scripts',
			'Scripts',
			null,
			$this->plugin_name
		);

		add_settings_field(
			'marketing_scripts',
			'Marketing Scripts (JS)',
			array( $this, 'field_marketing_scripts_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_scripts'
		);

		add_settings_field(
			'statistics_scripts',
			'Statistics Scripts (JS)',
			array( $this, 'field_statistics_scripts_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_scripts'
		);

		// Section: Styling
		add_settings_section(
			'swiss_simple_consent_styling',
			'Styling',
			null,
			$this->plugin_name
		);

		add_settings_field(
			'banner_bg_color',
			'Banner Background Color',
			array( $this, 'field_banner_bg_color_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_styling'
		);

		add_settings_field(
			'button_color',
			'Button Color',
			array( $this, 'field_button_color_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_styling'
		);
	}

	/**
	 * Sanitize options.
	 */
	public function sanitize_options( $input ) {
		$new_input = array();
		if ( isset( $input['enable_banner'] ) ) {
			$new_input['enable_banner'] = absint( $input['enable_banner'] );
		}

		// Allow scripts.
		// We trust the admin user here.

		if ( isset( $input['marketing_scripts'] ) ) {
			// Unslash to remove escape characters added by WordPress to $_POST
			$new_input['marketing_scripts'] = wp_unslash( $input['marketing_scripts'] );
		}

		if ( isset( $input['statistics_scripts'] ) ) {
			// Unslash to remove escape characters added by WordPress to $_POST
			$new_input['statistics_scripts'] = wp_unslash( $input['statistics_scripts'] );
		}

		if ( isset( $input['banner_bg_color'] ) ) {
			$new_input['banner_bg_color'] = sanitize_hex_color( $input['banner_bg_color'] );
		}

		if ( isset( $input['button_color'] ) ) {
			$new_input['button_color'] = sanitize_hex_color( $input['button_color'] );
		}

		return $new_input;
	}

	/**
	 * Field callbacks.
	 */
	public function field_enable_banner_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['enable_banner'] ) ? $options['enable_banner'] : 0;
		?>
		<input type="checkbox" name="<?php echo $this->plugin_name; ?>_options[enable_banner]" value="1" <?php checked( 1, $val ); ?> />
		<?php
	}

	public function field_marketing_scripts_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['marketing_scripts'] ) ? $options['marketing_scripts'] : '';
		?>
		<textarea name="<?php echo $this->plugin_name; ?>_options[marketing_scripts]" rows="5" cols="50" class="large-text code"><?php echo esc_textarea( $val ); ?></textarea>
		<p class="description">Enter your Marketing scripts (e.g., Facebook Pixel) here. Include &lt;script&gt; tags.</p>
		<?php
	}

	public function field_statistics_scripts_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['statistics_scripts'] ) ? $options['statistics_scripts'] : '';
		?>
		<textarea name="<?php echo $this->plugin_name; ?>_options[statistics_scripts]" rows="5" cols="50" class="large-text code"><?php echo esc_textarea( $val ); ?></textarea>
		<p class="description">Enter your Statistics scripts (e.g., Google Analytics) here. Include &lt;script&gt; tags.</p>
		<?php
	}

	public function field_banner_bg_color_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['banner_bg_color'] ) ? $options['banner_bg_color'] : '#ffffff';
		?>
		<input type="color" name="<?php echo $this->plugin_name; ?>_options[banner_bg_color]" value="<?php echo esc_attr( $val ); ?>" />
		<?php
	}

	public function field_button_color_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['button_color'] ) ? $options['button_color'] : '#0073aa';
		?>
		<input type="color" name="<?php echo $this->plugin_name; ?>_options[button_color]" value="<?php echo esc_attr( $val ); ?>" />
		<?php
	}


	/**
	 * Display the settings page.
	 */
	public function display_plugin_settings_page() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show error/update messages
		settings_errors( $this->plugin_name . '_messages' );

		// Tabs logic
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<a href="?page=<?php echo $this->plugin_name; ?>&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
				<a href="?page=<?php echo $this->plugin_name; ?>&tab=scripts" class="nav-tab <?php echo $active_tab == 'scripts' ? 'nav-tab-active' : ''; ?>">Scripts</a>
				<a href="?page=<?php echo $this->plugin_name; ?>&tab=styling" class="nav-tab <?php echo $active_tab == 'styling' ? 'nav-tab-active' : ''; ?>">Styling</a>
			</h2>

			<form action="options.php" method="post">
				<?php
				// Output security fields for the registered setting "swiss-simple-consent"
				settings_fields( $this->plugin_name );
				?>

				<table class="form-table" role="presentation">
				<?php
					if ( $active_tab == 'general' ) {
						do_settings_fields( $this->plugin_name, 'swiss_simple_consent_general' );
					} elseif ( $active_tab == 'scripts' ) {
						do_settings_fields( $this->plugin_name, 'swiss_simple_consent_scripts' );
					} elseif ( $active_tab == 'styling' ) {
						do_settings_fields( $this->plugin_name, 'swiss_simple_consent_styling' );
					}
				?>
				</table>

				<?php
				// Submit button
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}
}
