<?php

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

class Ipinfo_Response implements Geo_API_Response_Interface
{
	/**
	 * @var array{country: string, status:number}|false
	 */
	private array|false $response;
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
		return $this->response['country'];
	}

	/**
	 * @return mixed
	 */
	public function failed(): bool
	{
		if(!$this->response){
			return true;
		}
		return $this->response['status'] === 404 || $this->response['status'] === 'fail';
	}
}