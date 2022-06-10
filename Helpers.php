<?php

class Helpers
{
	
	public static function getPostBody(): ?array
	{
		$content_type = explode(";", $_SERVER['CONTENT_TYPE'])[0];
		switch ($content_type)
		{
			case 'multipart/form-data':
			{
				return $_POST;
			}
			break;
			
			case 'application/x-www-form-urlencoded':
			{
				$content = file_get_contents("php://input");
				$data = null;
				parse_str($content, $data);
				return $data;
			}
			
			case 'text/json':
			case 'application/json':
			{
				$content = file_get_contents("php://input");
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
	
	public static function getBaseUri(): string
	{
		return dirname($_SERVER['SCRIPT_NAME']);
	}
	
	public static function getRequestUri(): string
	{
		$uri = $_SERVER['REQUEST_URI'];
		
		$base_uri = Helpers::getBaseUri();
		if (substr($uri, 0, strlen($base_uri)) == $base_uri)
		{
			$uri = substr($uri, strlen($base_uri));
		}
		
		list($uri) = explode('?', $uri);
		return $uri;
	}
	
}
