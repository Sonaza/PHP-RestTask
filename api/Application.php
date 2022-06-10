<?php

declare(strict_types=1);

class Application
{
	
	private $restAPI;
	
	public function __construct()
	{
		$this->restAPI = new RestAPICore();
		
		$this->restAPI->add_endpoint("POST", "login", "AuthProvider@post_login");

		$this->restAPI->add_endpoint("GET", "getMovie", "MoviesProvider@get_movie", "AuthProvider@verify_authorization");
		$this->restAPI->add_endpoint("GET", "getBook", "BooksProvider@get_book", "AuthProvider@verify_authorization");
	}
	
	public function run()
	{
		header("Content-type: application/json");
		
		$response = $this->restAPI->route();
		echo json_encode($response);
	}
	
}
