<?php
/**
 * Main file that decides to do redirection based on user ip country.
 *
 * This class provides the logic to decide whether requests needs to be redirected to the most appropriate user
 * language. Supports multiple languages for a single country using comma-separated values.
 *
 * @package   Sagani_IP_Location_Multilingual_Redirection\src
 * @author    Eliasu Abraman
 * @copyright Copyright (c) 2025
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src;

use Exception;
use Sagani_IP_Location_Multilingual_Redirection\src\dto\Geo_Api_Response;

/**
 * Class Plugin
 *
 * Handles IP-based multilingual redirection logic for WordPress.
 *
 * @package Sagani_IP_Location_Multilingual_Redirection\src
 */
final class Plugin {

	/**
	 * Display notice when no settings value is set.
	 *
	 * @return void
	 */
	public static function setup(): void {
		if ( ! Settings::setting( 'geo_api_url' ) ) {
			self::error_notice( 'You must set you geo api credentials at Settings >> IP-based redirection' );
		}
	}

	/**
	 * Display error notice in the admin dashboard.
	 *
	 * @param string $message Error message to display.
	 * @return void
	 */
	public static function error_notice( string $message ): void {
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

	/**
	 * Get the user's IP address.
	 *
	 * @return string|null
	 */
	private static function user_ip(): ?string {
		foreach ( array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		) as $key ) {
			$value = isset( $_SERVER[ $key ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) : array();
			if ( ! empty( $value ) ) {
				foreach ( explode( ',', $value ) as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
						return $ip;
					}
				}
			}
		}
		return null;
	}

	/**
	 * Get preferred languages based on IP address.
	 *
	 * @param string $ip_address IP address of the user.
	 * @return array
	 */
	private static function user_languages( string $ip_address ): array {
		$cache = get_transient( $ip_address );

		if ( $cache ) {
			return $cache;
		}

		$url      = sprintf( Settings::setting( 'geo_api_url' ), $ip_address );
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$response_dto = new Geo_Api_Response(
			wp_remote_retrieve_body( $response ),
			Settings::setting( 'geo_api_provider' ),
			$response['response']['code']
		);

		if ( $response_dto->failed() ) {
			return array();
		}

		$country_code = $response_dto->country_code();

		$languages = Languages::get_language_by_country( $country_code );

		$languages = explode( ',', $languages );

		set_transient( $ip_address, $languages, 60 * 60 );

		return $languages;
	}

	/**
	 * Get current page translations.
	 *
	 * @return array
	 */
	private static function current_page_translations(): array {
		switch ( true ) {
			case is_singular( array( 'post', 'page' ) ):
				return pll_get_post_translations( get_the_ID() );

			case is_archive():
				return pll_get_term_translations( get_queried_object_id() );

			default:
				return array( 'en' => get_queried_object_id() );
		}
	}

	/**
	 * Get details about the current page including its locale and URLs.
	 *
	 * @return array
	 */
	private static function current_page(): array {
		$closure = match ( true ) {
			is_singular( array( 'post', 'page' ) ) => function (): array {
				$locale = pll_get_post_language( get_the_ID() );
				return array(
					'locale'      => $locale,
					'locale_link' => get_permalink( pll_get_post( get_the_ID(), $locale ) ),
					'link'        => get_permalink(),
				);
			},
			is_archive() => function (): array {
				$locale = pll_get_term_language( get_queried_object_id() );
				return array(
					'locale'      => $locale,
					'locale_link' => get_term_link( pll_get_term( get_queried_object_id(), $locale ) ),
					'link'        => get_term_link( get_queried_object_id() ),
				);
			},
			default => fn(): array => array(),
		};
		return $closure();
	}

	/**
	 * Get the locale of the current page.
	 *
	 * @return array|\PLL_Language|bool|int|string
	 */
	private static function current_page_locale(): array|\PLL_Language|bool|int|string {
		switch ( true ) {
			case is_singular( array( 'post', 'page' ) ):
				return pll_get_post_language( get_the_ID() );

			case is_archive():
				return pll_get_term_language( get_queried_object_id() );

			default:
				return 'en';
		}
	}

	/**
	 * Get the localized link for a given locale.
	 *
	 * @param string $locale Locale code.
	 * @return array|false|int|string|\WP_Error|\WP_Term|null
	 */
	private static function link( string $locale ) {
		switch ( true ) {
			case is_singular( array( 'post', 'page' ) ):
				return get_permalink( pll_get_post( get_the_ID(), $locale ) );

			case is_archive():
				return get_term_link( pll_get_term( get_queried_object_id(), $locale ) );

			default:
				return '';
		}
	}

	/**
	 * Redirects to the given URL.
	 *
	 * @param string $url URL to redirect to.
	 * @return void
	 */
	private static function redirect( string $url ): void {
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Add query strings to URLs from Polylang language switcher.
	 *
	 * @param string $url Original URL.
	 * @return string
	 */
	public static function filter_switch_url( string $url ) {
		if( empty($url)){
			$url = '/';
		}
		return add_query_arg( array( 'from_switcher' => 1 ), $url );
	}

	/**
	 * Check if a query variable is set.
	 *
	 * @param string $key Query variable key.
	 * @return bool
	 */
	private static function is_query_var( string $key ) {
		return isset( $_GET[ $key ] ) || get_query_var( $key ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Unset a query variable from the $_GET global.
	 *
	 * @param string $key Query variable key.
	 * @return void
	 */
	private static function remove_query_var( string $key ) {
		unset( $_GET[ $key ] );
	}

	/**
	 * Inits the plugin
	 *
	 * @return void
	 */
	public static function init() {
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( is_front_page() && is_home() ) {
			return;
		}

		$from_switcher = self::is_query_var( 'from_switcher' ); // phpcs:ignore;

		if ( $from_switcher ) {
			self::remove_query_var( 'from_switcher' );
			return;
		}

		$ip = self::user_ip();

		$user_languages = self::user_languages( $ip );

		if ( empty( $user_languages ) ) {
			return;
		}

		$page_translations = self::current_page_translations();

		$user_language = array_filter(
			$user_languages,
			function ( $user_lang ) use ( $page_translations ) {
				$user_locale = explode( '-', $user_lang )[0];
				return in_array( $user_locale, array_keys( $page_translations ), true );
			}
		);

		$current_page_locale = self::current_page_locale();
		$user_locale         = current( $user_language );

		if ( ( $user_locale === $current_page_locale ) ) {
			return;
		}

		if ( isset( $_REQUEST['t2g-default-locale'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( ! isset( $page_translations[ $user_locale ] ) ) {
			if ( ! in_array( 'en', array_keys( $page_translations ), true ) ) {
				return;
			}
			$_REQUEST['t2g-default-locale'] = 1;
			$url                            = add_query_arg( array( 't2g-default-locale' => 1 ), self::link( 'en' ) );
			self::redirect( $url );
		}

		self::redirect( self::link( $user_locale ) );
	}
}
