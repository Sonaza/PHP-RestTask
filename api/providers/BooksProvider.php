<?php

declare(strict_types=1);

class BooksProvider
{
	
	public function __construct()
	{
		
	}
	
	public function get_book(string $isbn = '')
	{
		echo "unga bunga babbla boo: " . $isbn;
	}	
	
}
