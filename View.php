<?php

declare(strict_types=1);

class ViewDoesNotExistException extends Exception {}

class View
{
	
	public static function load(string $view_file, array $data = [])
		{
		$view_file = 'views/' . $view_file;
		if (!file_exists($view_file))
		{
			throw new ViewDoesNotExistException("View file '$view_file' does not exist.");
		}
		
		foreach ($data as $key => $value)
		{
			${$key} = $value;
		}
		
		ob_start();
		include($view_file);
		return ob_get_clean();
	}

	public static function display(string $view_file, $data = [])
	{
		try
		{
			echo View::load($view_file, $data);
		}
		catch (ViewDoesNotExistException $e)
		{
			echo "Loading view '$view_file' failed.";
		}
	}
	
}
