<?php

declare(strict_types=1);

class AuthProvider
{
	
	public function post_login(): Response
	{
		$body = Helpers::getPostBody();
		
		// Rudimentary authorization for illustrative purposes, not for production use!
		if (array_key_exists('username', $body) && array_key_exists('password', $body))
		{
			if ($body['username'] == 'testuser' && $body['password'] == 'password')
			{
				$token = JSONWebToken::create(
				[
					'username' => $body['username'],
				], 'HS256');
				
				return new Response(
					Response::STATUS_OK,
					Response::CONTENT_TYPE_JSON,
					[
						'success' => true,
						'token'   => $token,
					]
				);
			}
		}
		
		return new Response(
			Response::STATUS_OK,
			Response::CONTENT_TYPE_JSON,
			[
				'success' => false,
				'error'   => 'Invalid credentials.',
			]
		);
	}
	
	public function verify_authorization(): bool
	{
		$bearer_token = Helpers::getAuthorizationBearerToken();
		if (is_null($bearer_token))
			return false;
		
		return JSONWebToken::verify($bearer_token);
	}
	
}
