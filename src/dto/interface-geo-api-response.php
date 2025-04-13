<?php

declare(strict_types=1);

namespace Sagani_IP_Location_Multilingual_Redirection\src\dto;

interface Geo_API_Response_Interface
{
	public function country_code();
	public function failed() : bool;
}