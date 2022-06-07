<?php

declare(strict_types=1);

require_once "Base64Url.php";

class HashNotSupportedException extends Exception {}
class InternalErrorException extends Exception {}

class JSONWebTokenManager
{
	const SUPPORTED_ALGORITHMS = [
		'HS256' => 'sha256',
	];
	
	static public function make_token(array $payload = [], string $hash_algo = "HS256"): string
	{
		// Verify the requested algorithm is supported
		$hash_algo = strtoupper($hash_algo);
		if (!array_key_exists($hash_algo, JSONWebTokenManager::SUPPORTED_ALGORITHMS))
		{
			throw new HashNotSupportedException("Algorithm '$hash_algo' is not supported.");
		}
		$hmac_algo = JSONWebTokenManager::SUPPORTED_ALGORITHMS[$hash_algo];
		
		// Create header and payload arrays
		$header = [
			"alg" => $hash_algo,
			"typ" => "JWT",
		];
		
		$payload = array_merge($payload,
		[
			"iat" => time(),
		]);
		
		$header_base64  = Base64Url::encode(json_encode($header));
		$payload_base64 = Base64Url::encode(json_encode($payload));
		if ($header_base64 === false || $payload_base64 === false)
		{
			throw new InternalErrorException("Failed to Base64Url encode header/payload.");
		}
		
		// Create a signature and encode it
		$hash_binary = hash_hmac(
			$hmac_algo,
			$header_base64 . '.' . $payload_base64,
			Config::JWT_SECRET_KEY,
			true
		);
		$signature_base64 = Base64Url::encode($hash_binary);
		if ($signature_base64 === false)
		{
			throw new InternalErrorException("Failed to Base64Url encode signature.");
		}
		
		// Great success, return a complete token
		return $header_base64 . '.' . $payload_base64 . '.' . $signature_base64;
	}
	
	static public function verify_token(string $token): bool
	{
		$token_segments = explode('.', $token, 3);
		if ($token_segments === false || count($token_segments) != 3)
		{
			return false;
		}
		list($header_base64, $payload_base64, $signature_base64) = $token_segments;
		
		// Attempt to decode token header and check it succeeded
		$header = Base64Url::decode($header_base64);
		if ($header === false || !is_string($header)) return false;
		
		$header = json_decode($header, true);
		if (is_null($header)) return false;
		
		// Require algorithm type and token type keys
		if (!array_key_exists('alg', $header) || !array_key_exists('typ', $header)) return false;
		
		// Verify token type (dunno if it realistically is ever anything else)
		if ($header['typ'] != 'JWT') return false;
		
		// Retrieve used hash algo and verify it is supported
		$hash_algo = strtoupper($header['alg']);
		if (!array_key_exists($hash_algo, JSONWebTokenManager::SUPPORTED_ALGORITHMS))
		{
			throw new HashNotSupportedException("Algorithm '$hash_algo' is not supported.");
		}
		$hmac_algo = JSONWebTokenManager::SUPPORTED_ALGORITHMS[$hash_algo];
		
		// Confirming that the supplied signature hash matches
		$hash_binary = hash_hmac(
			$hmac_algo,
			$header_base64 . '.' . $payload_base64,
			Config::JWT_SECRET_KEY,
			true
		);
		$signature_binary = Base64Url::decode($signature_base64);
		if ($signature_binary === false || !hash_equals($hash_binary, $signature_binary)) return false;
		
		// Decode payload and check the token is not yet expired or used before validity, if applicable
		$payload = Base64Url::decode($payload_base64);
		if ($payload === false || !is_string($payload)) return false;
		
		$payload = json_decode($payload, true);
		if (is_null($payload)) return false;
		
		if (array_key_exists('exp', $payload))
		{
			if (!is_numeric($payload['exp']) || $payload['exp'] < time()) return false;
		}
		
		if (array_key_exists('nbf', $payload))
		{
			if (!is_numeric($payload['nbf']) || $payload['nbf'] > time()) return false;
		}
		
		// All's good and token is good to go
		return true;
	}
}
