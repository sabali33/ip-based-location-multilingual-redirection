<?php
/**
 * Main plugin file. A point of entrance to the plugin.
 *
 * It defines the plugin constants, require classes, setup for admin notices and registering hooks.
 *
 * @package   Sagani_IP_Location_Multilingual_Redirection
 * @author    Eliasu Abraman
 * @copyright Copyright (c) 2025
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

/**
 *  Plugin Name: IP Location Redirection
 *  Plugin URI:  https://wordpress.org/plugins/ip-based-location-redirection
 *  Description: This plugin detects user location based on IP address and redirects the user to the right translated page
 *  Author:      Eliasu Abraman
 *  Text Domain: ip-location-polylang-redirection
 *  Domain Path: /languages
 *  License:     GPL v2 or later
 *  Requires    PHP: 8.0
 *  Version:     1.0.0
 */

namespace Sagani_IP_Location_Multilingual_Redirection;

use Exception;
use Sagani_IP_Location_Multilingual_Redirection\src\Plugin;
use Sagani_IP_Location_Multilingual_Redirection\src\Settings;
use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if ( ! defined( 'SAGANI_IP_REDIRECTION_PATH' ) ) {
	define( 'SAGANI_IP_REDIRECTION_PATH', plugin_dir_path( __FILE__ ) );
	define( 'SAGANI_IP_REDIRECTION_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Requires autoload file if it exists.
 *
 * @throws Exception Throws an exception when no autoload.php file is found.
 */
function autoload(): void {
	if ( ! file_exists( SAGANI_IP_REDIRECTION_PATH . '/vendor/autoload.php' ) ) {
		throw new Exception( 'Autoload file can not be found' );
	}
	require_once SAGANI_IP_REDIRECTION_PATH . '/vendor/autoload.php';
}

add_action(
	'plugins_loaded',
	static function (): void {

		try {
			autoload();
			register_activation_hook(
				__FILE__,
				static function () {
					if ( ! defined( 'POLYLANG' ) ) {
						throw new Exception( 'You must install Polylang plugin to use this extension' );
					}

					if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
						throw new Exception( 'The PHP version is not compatible' );
					}
					global $wp_version;
					if ( version_compare( $wp_version, '5.5.0', '<' ) ) {
						throw new Exception( 'The WordPress version is not compatible' );
					}
				}
			);

			Plugin::setup();
			Settings::init();

			add_action( 'template_redirect', array( Plugin::class, 'init' ) );
			add_filter( 'pll_the_language_link', array( Plugin::class, 'filter_switch_url' ) );
			$file = plugin_basename( __FILE__ );
			add_action( "uninstall_$file", array( Plugin::class, 'uninstall' ) );

		} catch ( Throwable | \Exception $exception ) {
			Plugin::error_notice( $exception->getMessage() );
		}
	}
);
