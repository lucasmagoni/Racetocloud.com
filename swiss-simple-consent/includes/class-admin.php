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

		// Section: Content & Links
		add_settings_section(
			'swiss_simple_consent_content',
			'Content & Links',
			null,
			$this->plugin_name
		);

		add_settings_field(
			'logo_url',
			'Logo URL',
			array( $this, 'field_logo_url_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_content'
		);

		add_settings_field(
			'banner_headline',
			'Banner Headline',
			array( $this, 'field_banner_headline_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_content'
		);

		add_settings_field(
			'banner_text',
			'Banner Text',
			array( $this, 'field_banner_text_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_content'
		);

		add_settings_field(
			'privacy_policy_url',
			'Privacy Policy URL',
			array( $this, 'field_privacy_policy_url_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_content'
		);

		add_settings_field(
			'impressum_url',
			'Impressum/Legal Notice URL',
			array( $this, 'field_impressum_url_cb' ),
			$this->plugin_name,
			'swiss_simple_consent_content'
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

		if ( isset( $input['logo_url'] ) ) {
			$new_input['logo_url'] = esc_url_raw( $input['logo_url'] );
		}

		if ( isset( $input['banner_headline'] ) ) {
			$new_input['banner_headline'] = sanitize_text_field( $input['banner_headline'] );
		}

		if ( isset( $input['banner_text'] ) ) {
			$new_input['banner_text'] = sanitize_textarea_field( $input['banner_text'] );
		}

		if ( isset( $input['privacy_policy_url'] ) ) {
			$new_input['privacy_policy_url'] = esc_url_raw( $input['privacy_policy_url'] );
		}

		if ( isset( $input['impressum_url'] ) ) {
			$new_input['impressum_url'] = esc_url_raw( $input['impressum_url'] );
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

	public function field_logo_url_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['logo_url'] ) ? $options['logo_url'] : '';
		?>
		<input type="url" name="<?php echo $this->plugin_name; ?>_options[logo_url]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" />
		<p class="description">Enter the URL of your site logo.</p>
		<?php
	}

	public function field_banner_headline_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['banner_headline'] ) ? $options['banner_headline'] : '';
		?>
		<input type="text" name="<?php echo $this->plugin_name; ?>_options[banner_headline]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" />
		<p class="description">Optional headline for the banner.</p>
		<?php
	}

	public function field_banner_text_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['banner_text'] ) ? $options['banner_text'] : '';
		?>
		<textarea name="<?php echo $this->plugin_name; ?>_options[banner_text]" rows="3" cols="50" class="large-text"><?php echo esc_textarea( $val ); ?></textarea>
		<p class="description">Override the default banner text.</p>
		<?php
	}

	public function field_privacy_policy_url_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['privacy_policy_url'] ) ? $options['privacy_policy_url'] : '';
		?>
		<input type="url" name="<?php echo $this->plugin_name; ?>_options[privacy_policy_url]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" />
		<?php
	}

	public function field_impressum_url_cb() {
		$options = get_option( $this->plugin_name . '_options' );
		$val = isset( $options['impressum_url'] ) ? $options['impressum_url'] : '';
		?>
		<input type="url" name="<?php echo $this->plugin_name; ?>_options[impressum_url]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" />
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
				<a href="?page=<?php echo $this->plugin_name; ?>&tab=content" class="nav-tab <?php echo $active_tab == 'content' ? 'nav-tab-active' : ''; ?>">Content & Links</a>
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
					} elseif ( $active_tab == 'content' ) {
						do_settings_fields( $this->plugin_name, 'swiss_simple_consent_content' );
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
