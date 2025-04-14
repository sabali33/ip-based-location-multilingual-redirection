<?php

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

class Geo_Api_Response implements Geo_API_Response_Interface
{
	private Geo_API_Response_Interface $response;
	public const IPINFO = 'ipinfo';
	public const IP_GEO_LOCATION = 'ipgeolocation';
	public const IP_API_LOCATION = 'ip-api';

	private int $response_code;
	public function __construct(mixed $response, string $provider, int $response_code)
	{
		if(self::IPINFO === $provider){
			$this->response = new Ipinfo_Response($response);
		}
		if(self::IP_GEO_LOCATION === $provider) {
			$this->response = new Ipgeolocation_Response($response);
		}
		if(self::IP_API_LOCATION === $provider){
			$this->response = new Ip_API_Response($response);
		}

		$this->response_code = $response_code;
	}

	public function country_code(): string
	{
		return $this->response->country_code();
	}


	public function failed(): bool
	{
		if(!in_array($this->response_code, [200,201])){
			return true;
		}
		return $this->response->failed();
	}
}