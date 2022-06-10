<?php

declare(strict_types=1);

class Client
{
	private $base_endpoint_url = '';
	
	public function __construct()
	{
		spl_autoload_register([$this, 'autoload']);
		
		// Avoids hardcoding the api URL, though that'd probably be better done in some config file
		$this->base_endpoint_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . Helpers::getBaseUri() . '/api';
	}
	
	private function autoload($class_name)
	{
		if (file_exists(__DIR__ . '/' . $class_name . ".php"))
		{
			require_once __DIR__ . '/' . $class_name . ".php";
		}
	}
	
	private function get_jwt_token(string $username, string $password): ?string
	{
		$request_data = [
			'username' => $username,
			'password' => $password,
		];
		
		$request_headers = [
			'User-Agent: Lamia Test Thingy',
		];
		
		$request_url = $this->base_endpoint_url . "/login";
		
		try
		{
			$response = Requests::post_json($request_url, $request_data, $request_headers);
		}
		catch (RequestsInvalidJSONException $e)
		{
			return null;
		}
		
		if ($response['status_code'] != 200 || $response['content']['success'] === false)
		{
			return null;
		}
		
		return $response['content']['token'];
	}
	
	private function make_data_query(string $request_url, array $request_data, string $jwt_token)
	{
		$request_headers = [
			'Authorization: Bearer ' . $jwt_token,
			'User-Agent: Lamia Test Thingy',
		];
		
		try
		{
			$response = Requests::get_json($request_url, $request_data, $request_headers);
		}
		catch (RequestsInvalidJSONException $e)
		{
			return "Response content not valid JSON";
		}
		
		if ($response['content']['success'] === false)
		{
			return "Error: ". $response['error'];
		}
		
		return json_encode($response['content'], JSON_PRETTY_PRINT);
	}
	
	public function run()
	{
		$data = [
			'base_endpoint_url' => $this->base_endpoint_url,
			
			// For this sample task these credentials are hardcoded in AuthProvider.
			'jwt_username'    => 'testuser',
			'jwt_password'    => 'password',
			
			'books_isbn'      => '9780330508117',
			'books_response'  => '',
			
			'movies_title'    => 'Iron Sky',
			'movies_year'     => '2012',
			'movies_plot'     => 'full',
			'movies_response' => '',
		];
		
		$data['jwt_token'] = $this->get_jwt_token($data['jwt_username'], $data['jwt_password']);
		
		// If auth token could not be obtained then making the requests won't be possible.
		if (!is_null($data['jwt_token']))
		{
			// Retrieve book info by ISBN
			$request_url = $this->base_endpoint_url . "/getBook";
			$request_data = [
				'isbn' => $data['books_isbn'],
			];
			$data['books_response'] = $this->make_data_query($request_url, $request_data, $data['jwt_token']);
			
			// Retrieve movie info by title and year,
			$request_url = $this->base_endpoint_url . "/getMovie";
			$request_data = [
				'title' => $data['movies_title'],
				'year'  => $data['movies_year'],
				'plot'  => $data['movies_plot'],
			];
			$data['movies_response'] = $this->make_data_query($request_url, $request_data, $data['jwt_token']);
		}
		
		header("Content-type: text/html; charset=utf-8");
		View::display("Main.php", $data);
	}
}

$client = new Client();
$client->run();
