<?php
/**
 * Unified GEO API Response Handler.
 *
 * This class acts as a wrapper for different GEO IP provider responses,
 * standardizing the interface to retrieve the country code and check for failures.
 *
 * Supported providers:
 * - IPinfo
 * - IP Geolocation
 * - IP-API
 *
 * @package   Sagani_IP_Location_Multilingual_Redirection
 * @subpackage Response
 * @implements Geo_API_Response_Interface
 */

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

/**
 * This class acts as a wrapper for different GEO IP provider responses,
 *  standardizing the interface to retrieve the country code and check for failures.
 */
class Geo_Api_Response implements Geo_API_Response_Interface {

	/**
	 * The response handler instance for a specific GEO API provider.
	 *
	 * @var Geo_API_Response_Interface
	 */
	private Geo_API_Response_Interface $response;

	/**
	 * Supported GEO API provider constants.
	 */
	public const IPINFO          = 'ipinfo';
	public const IP_GEO_LOCATION = 'ipgeolocation';
	public const IP_API_LOCATION = 'ip-api';

	/**
	 * HTTP response code from the API request.
	 *
	 * @var int
	 */
	private int $response_code;

	/**
	 * Constructor.
	 *
	 * @param mixed  $response       Raw API response (usually a JSON string).
	 * @param string $provider       One of the supported providers (see class constants).
	 * @param int    $response_code  HTTP response code returned from the API request.
	 */
	public function __construct( mixed $response, string $provider, int $response_code ) {
		if ( self::IPINFO === $provider ) {
			$this->response = new Ipinfo_Response( $response );
		}
		if ( self::IP_GEO_LOCATION === $provider ) {
			$this->response = new Ipgeolocation_Response( $response );
		}
		if ( self::IP_API_LOCATION === $provider ) {
			$this->response = new Ip_API_Response( $response );
		}

		$this->response_code = $response_code;
	}

	/**
	 * Get the 2-letter ISO country code from the API response.
	 *
	 * @return string Country code (e.g., "US", "GH").
	 */
	public function country_code(): string {
		return $this->response->country_code();
	}

	/**
	 * Determine if the API request or response failed.
	 *
	 * This method checks both the HTTP status code and
	 * the validity of the internal response handler.
	 *
	 * @return bool True if the API call failed, false otherwise.
	 */
	public function failed(): bool {
		if ( ! in_array( $this->response_code, array( 200, 201 ), true ) ) {
			return true;
		}
		return $this->response->failed();
	}
}
