<?php

declare(strict_types=1);

class Input
{
	
	public static function get($key, $default = null)
	{
		if (array_key_exists($key, $_GET))
		{
			return $_GET[$key];
		}
		return $default;
	}
	
	public static function post($key, $default = null)
	{
		if (array_key_exists($key, $_GET))
		{
			return $_GET[$key];
		}
		return $default;
	}
	
}
