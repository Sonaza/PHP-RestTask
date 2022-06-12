<?php

declare(strict_types=1);

require_once "Bootstrap.php";

class Application
{
	
	private $restAPI;
	
	public function __construct()
	{
		$this->restAPI = new RestAPI();
		
		$this->restAPI->add_endpoint("POST", "login", "AuthProvider@post_login");

		$this->restAPI->add_endpoint("GET", "getMovie", "MoviesProvider@get_movie", "AuthProvider@verify_authorization");
		$this->restAPI->add_endpoint("GET", "getBook", "BooksProvider@get_book", "AuthProvider@verify_authorization");
	}
	
	public function run()
	{
		$response = $this->restAPI->route();
		$response->output();
	}
	
}

$app = new Application();
$app->run();
