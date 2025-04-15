<?php
/**
 * IPGeolocation.io API Response Handler.
 *
 * This class parses and normalizes the response from the IPGeolocation.io API
 * to implement a standard interface for IP-based location detection.
 *
 * @package Sagani_IP_Location_Multilingual_Redirection\src\dto
 * @implements Geo_API_Response_Interface
 */

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

/**
 *  This class parses and normalizes the response from the IPGeolocation.io API
 *  to implement a standard interface for IP-based location detection.
 */
class Ipgeolocation_Response implements Geo_API_Response_Interface {

	/**
	 * Decoded JSON response from the IPGeolocation.io API.
	 *
	 * Expected to contain at least a "country_code2" key.
	 *
	 * @var array{country_code2?: string, message?: string}|false
	 */
	private array|false $response;

	/**
	 * Constructor.
	 *
	 * @param string $response Raw JSON response from the IPGeolocation.io API.
	 */
	public function __construct( string $response ) {
		$this->response = json_decode( $response, true );
	}

	/**
	 * Get the ISO 2-letter country code.
	 *
	 * @return string Country code (e.g., "US", "NG").
	 */
	public function country_code(): string {
		return $this->response['country_code2'] ?? '';
	}

	/**
	 * Check if the API response indicates a failure.
	 *
	 * If the response is false or contains an error message, it's considered failed.
	 *
	 * @return bool True if the response failed.
	 */
	public function failed(): bool {
		return ! $this->response || isset( $this->response['message'] );
	}
}
