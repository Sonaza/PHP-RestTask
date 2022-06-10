<?php

declare(strict_types=1);

class RequestsInvalidJSONException extends Exception {}

class Requests
{
	
	public static function request(string $method, string $url, ?array $data = null, array $headers = [])
	{
		$curl = curl_init();
		
		switch ($method)
		{
			case 'GET':
				if (!is_null($data))
				{
					$url .= '?' . http_build_query($data);
				}
			break;
			
			case 'POST':
			{
				curl_setopt($curl, CURLOPT_POST, 1);
				if (!is_null($data))
				{
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				}
			}
			break;
		}
		
		// echo $url . "\n\n";
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		
		curl_setopt($curl, CURLOPT_HTTPHEADER,
			array_merge($headers, [
				// 'Content-Type: application/json',
			])
		);
		
		$response = curl_exec($curl);
		
		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		
		// Extract headers formatted as array rows
		$response_headers = trim(substr($response, 0, $header_size));
		$response_headers = explode("\r\n\r\n", $response_headers);
		foreach ($response_headers as &$headers)
		{
			$headers = explode("\r\n", $headers);
		}
		
		// Extract response content
		$response_body = substr($response, $header_size);
		
		$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$effective_url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
		
		curl_close($curl);
		
		return [
			'status_code'   => $status_code,
			'effective_url' => $effective_url,
			'headers'       => $response_headers,
			'content'       => $response_body,
		];
	}
	
	public static function get(string $url, ?array $data = null, array $headers = [])
	{
		return Requests::request('GET', $url, $data, $headers);
	}
	
	public static function get_json(string $url, ?array $data = null, array $headers = [])
	{
		$response = Requests::get($url, $data, $headers);
		
		$response['content'] = json_decode($response['content'], true);
		if (is_null($response['content']))
		{
			throw new RequestsInvalidJSONException("Request response is invalid JSON, parsing failed.");
		}
		return $response;
	}
	
	
	public static function post(string $url, ?array $data = null, array $headers = [])
	{
		return Requests::request('POST', $url, $data, $headers);
	}
	
	public static function post_json(string $url, ?array $data = null, array $headers = [])
	{
		$response = Requests::post($url, $data, $headers);
		
		$response['content'] = json_decode($response['content'], true);
		if (is_null($response['content']))
		{
			throw new RequestsInvalidJSONException("Request response is invalid JSON, parsing failed.");
		}
		return $response;
	}
	
}
