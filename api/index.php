<?php

require_once "Bootstrap.php";

$restAPI = new RestAPICore();

$restAPI->add_endpoint("POST", "login", "AuthProvider@post_login");

$restAPI->add_endpoint("GET", "getMovie", "MoviesProvider@get_movie", "AuthProvider@verify_authorization");
$restAPI->add_endpoint("GET", "getBook", "BooksProvider@get_book", "AuthProvider@verify_authorization");


// header("Content-type: application/json");
header("Content-type: text/plain");

echo json_encode($restAPI->route());
