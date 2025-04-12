<?php

declare(strict_types=1);

/**
 *  Plugin Name: IP Location Polylang Redirection
 *  Plugin URI:  https://talents2germany.com
 *  Description: This plugin detects user location based on IP address and redirects the user to the right translated page
 *  Author:      Eliasu Abraman
 *  Text Domain: talents2germany-ip-redirection
 *  Domain Path: /languages
 *  License:     GPL v2 or later
 *  Requires    PHP: 8.0
 *  Version:     1.0.5
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if(!defined('T2G_IP_REDIRECTION_PATH')){
	define( 'T2G_IP_REDIRECTION_PATH', plugin_dir_path( __FILE__ ) );
	define( 'T2G_IP_REDIRECTION_URL', plugin_dir_url( __FILE__ ) );
}
function T2G_setup(){
	if(!defined( 'GEO_API_URL')){
		$api_url = getenv('GEO_API_URL');
		if(!$api_url){
			throw new Exception("You must define GEO_API_URL in your wp-config.php file");
		}
		define('GEO_API_URL' , $api_url);
	}
}

require_once T2G_IP_REDIRECTION_PATH .'/T2G_Plugin.php';

function T2G_error_notice( string $message ): void {
	foreach ( array( 'admin_notices', 'network_admin_notices' ) as $hook ) {
		add_action(
			$hook,
			static function () use ( $message ) {
				$class = 'notice notice-error';

				printf(
					'<div class="%1$s"><p>%2$s</p></div>',
					esc_attr( $class ),
					wp_kses_post( $message )
				);
			}
		);
	}
}

add_action(
	'plugins_loaded',
	static function (): void {

		try {
			register_activation_hook(
				__FILE__ ,
				static function(){
					if(!defined('POLYLANG')){
						throw new Exception("You must install Polylang plugin to use this extension");
					}

					if(version_compare(PHP_VERSION, "7.4", "<")){
						throw new Exception("The PHP version is not compatible");
					}
					global $wp_version;
					if(version_compare($wp_version, "5.5.0", "<")){
						throw new Exception("The WordPress version is not compatible");
					}
				}
			);

			T2G_setup();
			add_action('template_redirect', [T2G_Plugin::class, 'init']);
			add_filter('pll_the_language_link', [T2G_Plugin::class, 'filter_switch_url']);

		} catch ( Throwable|\Exception $exception ) {
			T2G_error_notice( $exception->getMessage() );
		}
	}
);