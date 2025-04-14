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
		$this->response =  json_decode($response, true);
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
		$header = wp_remote_retrieve_headers($this->response);

		return isset($this->response['status']) && ($this->response['status'] === 404 || $this->response['status'] === 'fail');
	}
}