<?php

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src;

class Plugin_Settings {

	private static Plugin_Settings $instance;
	private function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_plugin_settings' ] );
	}

	public static function init(): void
	{
		if(isset(self::$instance)){
			return;
		}

		self::$instance = new self();
	}

	// Add menu item under "Settings"
	public function add_admin_menu(): void
	{
		add_options_page(
			'IP-based Redirection', // Page title
			'IP-based Redirection',          // Menu title
			'manage_options',     // Capability required
			'sagani-ip-based-redirection-settings',  // Menu slug
			[ $this, 'create_admin_page' ] // Callback to render page
		);
	}

	// Register settings, sections, and fields
	public function register_plugin_settings() {
		register_setting(
			'sagani_ip_based_redirection_settings_group', // Option group
			'sagani_ip_based_redirection_settings',       // Option name (stored in wp_options)
			[ $this, 'sanitize_settings' ] // Sanitization callback
		);

		// Add a settings section
		add_settings_section(
			'sagani_ip_redirection_main_section',
			__('Main Settings', 'sagani-ip-location-redirection'),
			[ $this, 'print_section_info' ],
			'sagani-ip-based-redirection-settings'
		);

		// Add fields to the section
		add_settings_field(
			'geo_api_url',
			__('GEO API url', 'sagani-ip-location-redirection'),
			[ $this, 'geo_api_url_callback' ],
			'sagani-ip-based-redirection-settings',
			'sagani_ip_redirection_main_section',

		);
	}

	// Sanitize input before saving to database
	public function sanitize_settings( $input ) {
		$sanitized = [];
		if ( isset( $input['geo_api_url'] ) ) {
			$sanitized['geo_api_url'] = sanitize_text_field( $input['geo_api_url'] );
		}

		return $sanitized;
	}

	// Render the settings page
	public function create_admin_page() {
		?>
		<div class="wrap">
			<h1><?php _e('IP-Based Location Redirection Settings', 'sagani-ip-location-redirection') ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'sagani_ip_based_redirection_settings_group' ); // Output security fields
				do_settings_sections( 'sagani-ip-based-redirection-settings' );  // Render sections/fields
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	// Section description
	public function print_section_info() {
		printf("<p>%s</p>", __('Configure your plugin settings below:', 'sagani-ip-location-redirection') );
	}

	// Field callbacks (render form inputs)
	public function geo_api_url_callback() {
		$options = get_option( 'sagani_ip_based_redirection_settings' );
		$value = $options['geo_api_url'] ?? '';
		echo '<div><input type="text" name="sagani_ip_based_redirection_settings[geo_api_url]" value="' . esc_attr( $value ) . '" class="regular-text"></div>';
        echo '<i>You can get a Geo API URL from these sites:</i>';
        ?>
        <ul>
            <li><a href="https://api.ipgeolocation.io">IP GEO Location</a>. <?php printf(__('For example: <code>%s</code>','sagani-ip-location-redirection'),'https://api.ipgeolocation.io/ipgeo?ip=%s&apiKey=xxxxxx6fcdb7874692'); ?></li>
            <li><a href="https://ipinfo.io">IPinfo</a>. <?php printf(__('For example: <code>%s</code>','sagani-ip-location-redirection'),'https://ipinfo.io/%s?token=xxxxxx6fcdb7874692'); ?> </li>
            <li><a href="https://members.ip-api.com/">IP API</a>. <?php printf(__('For example: <code>%s</code>','sagani-ip-location-redirection'),'http://ip-api.com/php/%s'); ?></li>
        </ul>
        <p>
            <b>Note: When setting the URL, make sure to append ip=%s as a query string for the user IP to be interpolated</b>
        </p>
        <?php
	}

	public static function setting(string $key)
	{
        $options = get_option( 'sagani_ip_based_redirection_settings' );
        return $options[$key] ?? false;
    }
}