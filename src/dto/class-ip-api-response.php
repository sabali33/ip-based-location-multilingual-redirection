<?php

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

class Ip_API_Response implements Geo_API_Response_Interface
{
	/**
	 * @var array{countryCode: string, status: string}|false|null
	 */
	private array|null|bool $response;
	/**
	 * @param string $response
	 */
	public function __construct(string $response)
	{
		$this->response = unserialize($response);
	}

	/**
	 * @return string
	 */
	public function country_code(): string
	{
		return $this->response['countryCode'];
	}

	public function failed(): bool
	{
		if(!$this->response){
			return true;
		}

		return $this->response['status'] === 'fail';
	}
}