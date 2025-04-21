<?php
/**
 * Plugin Settings Class
 *
 * Handles admin settings page for configuring GEO API provider and endpoint used
 * for IP-based language redirection in WordPress.
 *
 * @package Sagani_IP_Location_Multilingual_Redirection
 * @subpackage Admin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src;

use Sagani_IP_Location_Multilingual_Redirection\src\dto\Geo_Api_Response;

/**
 * Handles plugin settings page and configuration storage.
 */
class Settings {
	const SAGANI_IP_BASED_REDIRECTION_SETTINGS = 'sagani_ip_based_redirection_settings';
	const AUTO_REDIRECT_LOCALE_URL             = 'auto_redirect_locale_url_';

	/**
	 * Singleton instance of the settings class.
	 *
	 * @var Settings
	 */
	private static Settings $instance;

	/**
	 * Constructor.
	 * Registers admin hooks for a menu and settings.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
	}

	/**
	 * Initializes the settings singleton.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( isset( self::$instance ) ) {
			return;
		}

		self::$instance = new self();
	}

	/**
	 * Adds the plugin settings page under the Settings menu.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_options_page(
			'IP-based Redirection',
			'IP-based Redirection',
			'manage_options',
			'ip-based-redirection-settings',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Registers plugin settings, sections, and fields.
	 *
	 * @return void
	 */
	public function register_plugin_settings(): void {
		register_setting(
			'sagani_ip_based_redirection_settings_group',
			self::SAGANI_IP_BASED_REDIRECTION_SETTINGS,
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'sagani_ip_redirection_main_section',
			__( 'Main Settings', 'ip-location-polylang-redirection' ),
			array( $this, 'print_section_info' ),
			'ip-based-redirection-settings'
		);

		add_settings_field(
			'geo_api_provider',
			__( 'GEO API Provider', 'ip-location-polylang-redirection' ),
			array( $this, 'geo_api_provider_callback' ),
			'ip-based-redirection-settings',
			'sagani_ip_redirection_main_section'
		);

		add_settings_field(
			'geo_api_url',
			__( 'GEO API URL', 'ip-location-polylang-redirection' ),
			array( $this, 'geo_api_url_callback' ),
			'ip-based-redirection-settings',
			'sagani_ip_redirection_main_section'
		);

		if ( is_multisite() && is_main_site() ) {
			add_settings_section(
				'sagani_ip_redirection_multisite_section',
				__( 'Multisite Redirection Settings', 'ip-location-polylang-redirection' ),
				array( $this, 'print_section_info' ),
				'ip-based-redirection-settings',
				array( 'is_multisite' => true )
			);
			$supported_translations = self::supported_languages();
			foreach ( $supported_translations as $supported_translation ) {
				add_settings_field(
					"auto_redirect_locale_url_$supported_translation",
					sprintf(
							/* translators: %s: locale  */
						__( 'Auto redirect url for <i>%s</i> users ', 'ip-location-polylang-redirection' ),
						$supported_translation
					),
					array( $this, 'auto_redirect_locale_url_mapping_callback' ),
					'ip-based-redirection-settings',
					'sagani_ip_redirection_multisite_section',
					array( 'locale' => $supported_translation ),
				);
			}
		}
	}

	/**
	 * Sanitizes the plugin settings.
	 *
	 * @param array{geo_api_url: string, geo_api_provider: string, auto_redirect_locale_url_:string } $input Input data.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();
		$error     = null;

		$provider            = sanitize_text_field( $input['geo_api_provider'] );
		$api_url             = sanitize_text_field( $input['geo_api_url'] );
		$invalid_url_message = 'API is not valid';

		switch ( $provider ) {
			case Geo_Api_Response::IP_GEO_LOCATION:
				if ( ! str_contains( $api_url, Geo_Api_Response::IP_GEO_LOCATION ) ) {
					$error = $invalid_url_message;
				}
				break;
			case Geo_Api_Response::IPINFO:
				if ( ! str_contains( $api_url, Geo_Api_Response::IPINFO ) ) {
					$error = $invalid_url_message;
				}
				break;
			default:
				if ( ! str_contains( $api_url, Geo_Api_Response::IP_API_LOCATION ) ) {
					$error = $invalid_url_message;
				}
				break;
		}
		if ( is_multisite() && is_main_site() ) {
			foreach ( self::supported_languages() as $language ) {
				$option_key = "auto_redirect_locale_url_$language";
				$value      = isset( $_POST[ $option_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $option_key ] ) ) : '';

				if ( ! wp_parse_url( $value, PHP_URL_HOST ) ) {
					$error = $invalid_url_message;
					continue;
				}
				if ( isset( $_POST[ $option_key ] ) ) {
					$sanitized[ $option_key ] = $value;
				}
			}
		}

		if ( $error ) {
			add_settings_error(
				self::SAGANI_IP_BASED_REDIRECTION_SETTINGS,
				'sagani_ip_redirection_error',
				$error,
				'error'
			);
			return array();
		}

		if ( isset( $input['geo_api_url'] ) ) {
			$sanitized['geo_api_url'] = sanitize_text_field( $input['geo_api_url'] );
		}

		if ( isset( $input['geo_api_provider'] ) ) {
			$sanitized['geo_api_provider'] = sanitize_text_field( $input['geo_api_provider'] );
		}
		delete_transient( '' );
		return $sanitized;
	}

	/**
	 * Outputs the HTML for the admin settings page.
	 *
	 * @return void
	 */
	public function create_admin_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'IP-Based Location Redirection Settings', 'ip-location-polylang-redirection' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'sagani_ip_based_redirection_settings_group' );
				do_settings_sections( 'ip-based-redirection-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Prints a short description above the settings section.
	 *
	 * @param array $args Passed arguments from the add section function.
	 * @return void
	 */
	public function print_section_info( array $args ): void {
		if ( isset( $args['is_multisite'] ) && $args['is_multisite'] ) {
			printf(
				'<p>%s</p>',
				esc_html__( 'Configure Multisite redirection:', 'ip-location-polylang-redirection' )
			);
			return;
		}
		printf(
			'<p>%s</p>',
			esc_html__( 'Configure plugin settings below:', 'ip-location-polylang-redirection' )
		);
	}

	/**
	 * Renders the input field for the Geo API URL setting.
	 *
	 * @return void
	 */
	public function geo_api_url_callback(): void {
		$options = get_option( self::SAGANI_IP_BASED_REDIRECTION_SETTINGS );
		$value   = $options['geo_api_url'] ?? '';
		echo '<div><input type="text" name="sagani_ip_based_redirection_settings[geo_api_url]" value="' . esc_attr( $value ) . '" class="regular-text"></div>';
		echo '<i>' . esc_html__( 'You can get a Geo API URL from these sites:', 'ip-location-polylang-redirection' ) . '</i>';
		?>
		<ul>
			<li>
				<a href="https://api.ipgeolocation.io">IP GEO Location</a>.
				<?php

					printf(
						wp_kses(
						/* translators: %s: An endpoint to Ipgeolocation  */
							__( 'For example: <code>%s</code>', 'ip-location-polylang-redirection' ),
							array(
								'code' => array(),
							),
						),
						'https://api.ipgeolocation.io/ipgeo?ip=%s&apiKey=xxxxxx6fcdb7874692'
					);
				?>
			</li>
			<li>
				<a href="https://ipinfo.io">IPinfo</a>.
				<?php

					printf(
						wp_kses(
						/* translators: %s: An endpoint to Ipinfo  */
							__( 'For example: <code>%s</code>', 'ip-location-polylang-redirection' ),
							array( 'code' => array() )
						),
						'https://ipinfo.io/%s?token=xxxxxx6fcdb7874692'
					);
				?>
				</li>
			<li>
				<a href="https://members.ip-api.com/">IP API</a>.
				<?php

					printf(
						wp_kses(
							/* translators: %s: An endpoint to ip-api.com  */
							__( 'For example: <code>%s</code>', 'ip-location-polylang-redirection' ),
							array( 'code' => array() )
						),
						'http://ip-api.com/json/%s'
					);
				?>
			</li>
		</ul>
		<p><b>
		<?php
			/* translators: %s: Not a translation placeholder  */
			esc_html_e( 'Note: When setting the URL, make sure to append ip=%s as a query string for the user IP to be interpolated.', 'ip-location-polylang-redirection' );
		?>
			</b></p>
		<?php
	}

	/**
	 * A callback to display input fields for supported site locale.
	 *
	 * @param array $args Arguments from add settings function call.
	 * @return void
	 */
	public function auto_redirect_locale_url_mapping_callback( array $args ): void {
		['locale' => $locale] = $args;
		$option_key           = self::AUTO_REDIRECT_LOCALE_URL . "$locale";
		$value                = self::setting( $option_key );

		?>

		<input type="url" name="<?php echo esc_attr( $option_key ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="https://..." />

		<?php
	}

	/**
	 * Renders the select field for choosing the API provider.
	 *
	 * @return void
	 */
	public function geo_api_provider_callback(): void {
		$value     = self::setting( 'geo_api_provider' );
		$providers = array(
			Geo_Api_Response::IP_API_LOCATION,
			Geo_Api_Response::IP_GEO_LOCATION,
			Geo_Api_Response::IPINFO,
		);
		?>
		<select name="sagani_ip_based_redirection_settings[geo_api_provider]" id="geo-api-provider" class="regular-text">
			<?php foreach ( $providers as $provider ) : ?>
				<option value="<?php echo esc_attr( $provider ); ?>" <?php selected( $value, $provider ); ?>>
					<?php echo esc_html( ucfirst( $provider ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Retrieves a single setting value by key.
	 *
	 * @param string $key The setting key to retrieve.
	 * @return string|false The setting value or false if not found.
	 */
	public static function setting( string $key ): bool|string {
		$options = get_option( self::SAGANI_IP_BASED_REDIRECTION_SETTINGS );
		return $options[ $key ] ?? false;
	}

	/**
	 * A callback function to run when the plugin is uninstalled.
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		update_option( self::SAGANI_IP_BASED_REDIRECTION_SETTINGS, null );
	}

	/**
	 * Get auto redirect URL for a locale
	 *
	 * @param string $locale A locale.
	 * @return bool|string
	 */
	public static function auto_redirect_locale_url( string $locale ): bool|string {
		if ( ! in_array( $locale, self::supported_languages(), true ) ) {
			$locale = 'en';
		}

		$option_key = self::AUTO_REDIRECT_LOCALE_URL . $locale;

		return self::setting( $option_key );
	}

	/**
	 * Returns languages supported by the main site.
	 *
	 * @return array
	 */
	public static function supported_languages(): array {
		return pll_languages_list();
	}
}
