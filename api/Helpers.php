<?php

class Helpers
{
	
	public static function getPostBody(): ?array
	{
		$content = file_get_contents("php://input");
		switch ($_SERVER['CONTENT_TYPE'])
		{
			case 'application/x-www-form-urlencoded':
			{
				$data = null;
				parse_str($content, $data);
				return $data;
			}
			
			case 'text/json':
			case 'application/json':
			{
				return json_decode($content, true);
			}
		}
		
		return null;
	}
	
	public static function getAuthorizationBearerToken(): ?string
	{
		$headers = array_change_key_case(apache_request_headers());
		
		if (array_key_exists('authorization', $headers))
		{
			$prefix = 'Bearer ';
			if (substr($headers['authorization'], 0, strlen($prefix)) == $prefix)
			{
				return substr($headers['authorization'], strlen($prefix));
			}
		}
		
		return null;
	}
	
	public static function getRequestUri(): string
	{
		$uri = $_SERVER['REQUEST_URI'];
		
		if (substr($uri, 0, strlen(Config::BASE_URI)) == Config::BASE_URI)
		{
			$uri = substr($uri, strlen(Config::BASE_URI));
		}
		
		list($uri) = explode('?', $uri);
		return $uri;
	}
	
}
