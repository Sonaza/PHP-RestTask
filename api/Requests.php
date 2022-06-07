<?php

declare(strict_types=1);

class Requests
{
	
	public static function request(string $method, string $url, ?array $data = null, array $headers = [])
	{
		$curl = curl_init();
		
		switch ($method)
		{
			case 'POST':
			{
				curl_setopt($curl, CURLOPT_POST, 1);
				if (!is_null($data))
				{
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				}
			}
			break;
			
			default:
				if (!is_null($data))
				{
					$url .= '?' . http_build_query($data);
				}
			break;
		}
		
		echo $url;
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($curl, CURLOPT_HTTPHEADER,
			array_merge($headers, [
				'Content-Type: application/json',
			])
		);
		
		$response = curl_exec($curl);
		$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		curl_close($curl);
		
		return [
			'status_code' => $status_code,
			'content'     => $response,
		];
	}
	
	public static function get(string $url, ?array $data = null, array $headers = [])
	{
		return Requests::request('GET', $url, $data, $headers);
	}
	
	public static function post(string $url, ?array $data = null, array $headers = [])
	{
		return Requests::request('POST', $url, $data, $headers);
	}
	
}
