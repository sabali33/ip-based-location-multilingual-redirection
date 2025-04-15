<?php
/**
 * An interface for all geo api providers.
 *
 * @package Sagani_IP_Location_Multilingual_Redirection\src\dto
 */

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

interface Geo_API_Response_Interface {

	/**
	 * Returns the ISO 2 of country code.
	 *
	 * @return string
	 */
	public function country_code(): string;

	/**
	 * The return value of a geo api client request.
	 *
	 * @return bool
	 */
	public function failed(): bool;
}
