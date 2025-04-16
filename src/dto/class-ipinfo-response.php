<?php
/**
 * Handles response parsing for the IPinfo GEO IP provider.
 *
 * This class decodes the JSON response returned by the IPinfo API
 * and provides standardized methods to access country code and failure status.
 *
 * @package Sagani_IP_Location_Multilingual_Redirection\src\dto
 * @subpackage Response
 * @implements Geo_API_Response_Interface
 */

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

/**
 *  This class decodes the JSON response returned by the IPinfo API
 *  and provides standardized methods to access country code and failure status.
 */
class Ipinfo_Response implements Geo_API_Response_Interface {

	/**
	 * Decoded JSON response from IPinfo API.
	 *
	 * The response typically contains the "country" field and may include a "status" field
	 * indicating success or failure.
	 *
	 * @var array{country: string, status?: int|string}|false
	 */
	private array|false $response;

	/**
	 * Constructor.
	 *
	 * @param string $response Raw JSON response string from the IPinfo API.
	 */
	public function __construct( string $response ) {
		$this->response = json_decode( $response, true );
	}

	/**
	 * Get the ISO 2-letter country code from the response.
	 *
	 * Example return values: "US", "DE", "GH".
	 *
	 * @return string Country code.
	 */
	public function country_code(): string {
		return $this->response['country'] ?? '';
	}

	/**
	 * Determine if the API call failed based on the response structure.
	 *
	 * Checks if the response is invalid (i.e., not JSON or null),
	 * or if it contains a "status" key that indicates an error.
	 *
	 * @return bool True if the request failed or response is invalid.
	 */
	public function failed(): bool {
		if ( ! $this->response ) {
			return true;
		}

		return isset( $this->response['status'] ) && ( 404 === $this->response['status'] || 'fail' === $this->response['status'] );
	}
}
