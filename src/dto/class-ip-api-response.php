<?php
/**
 * IP-API.com API Response Handler.
 *
 * Handles responses from the IP-API.com service and normalizes them to match
 * the Geo_API_Response_Interface format.
 *
 * @package Sagani_IP_Location_Multilingual_Redirection\src\dto
 * @implements Geo_API_Response_Interface
 */

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

/**
 *  Handles responses from the IP-API.com service and normalizes them to match
 *  the Geo_API_Response_Interface format.
 */
class Ip_API_Response implements Geo_API_Response_Interface {

	/**
	 * Decoded JSON response from IP-API.com.
	 *
	 * Expected to contain "countryCode" and "status".
	 *
	 * @var array{countryCode?: string, status?: string}|false
	 */
	private array|false $response;

	/**
	 * Constructor.
	 *
	 * @param string $response Raw JSON response string from the IP-API.com service.
	 */
	public function __construct( string $response ) {
		$this->response = json_decode( $response, true );
	}

	/**
	 * Retrieve the 2-letter ISO country code from the response.
	 *
	 * @return string Country code (e.g., "GB", "IN").
	 */
	public function country_code(): string {
		return $this->response['countryCode'] ?? '';
	}

	/**
	 * Determine if the API response is a failure.
	 *
	 * The response is considered a failure if it's invalid or if the "status" is not "success".
	 *
	 * @return bool True if failed.
	 */
	public function failed(): bool {
		return ! $this->response || ( isset( $this->response['status'] ) && 'success' !== $this->response['status'] );
	}
}
