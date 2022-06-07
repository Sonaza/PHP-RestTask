<?php

declare(strict_types=1);

// Implements the Base64Url variant of Base64 that PHP doesn't come with built in.

final class Base64Url
{
	
	public static function encode($str)
	{
		$str = base64_encode($str);
		if ($str === false)
		{
			return false;
		}
		return rtrim(strtr($str, '+/', '-_'), '=');
	}
	
	public static function decode($str)
	{
		$str = strtr($str, '-_', '+/');
		return base64_decode($str, true);
	}
	
}
