<?php

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

class Ipgeolocation_Response implements Geo_API_Response_Interface
{
	/**
	 * @var array{country_code2: string}|false
	 */
	private array|false $response;
	/**
	 * @param mixed $response
	 */
	public function __construct(mixed $response)
	{
		$this->response = $response;
	}

	/**
	 * @return string
	 */
	public function country_code(): string
	{
		return $this->response['country_code2'];
	}

	public function failed(): bool
	{
		return !$this->response;
	}
}