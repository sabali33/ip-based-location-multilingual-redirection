<?php

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

class Geo_Api_Response implements Geo_API_Response_Interface
{
	private Geo_API_Response_Interface $response;
	public const IPINFO = 'ipinfo';
	public const IP_GEO_LOCATION= 'ipgeolocation';
	public const IP_API_LOCATION= 'ip-api';
	public function __construct(mixed $response, string $provider)
	{
		if('ipinfo' === $provider){
			$this->response = new Ipinfo_Response($response);
		}
		if('ipgeolocation' === $provider){
			$this->response = new Ipgeolocation_Response($response);
		}
		if('ip-api' === $provider){
			$this->response = new Ip_API_Response($response);
		}
	}
	/**
	 * @return mixed
	 */
	public function country_code(): mixed
	{
		return $this->response->country_code();
	}

	public function locale(string $country_code)
	{
		
	}

	public function failed(): bool
	{
		return $this->response->failed();
	}
}