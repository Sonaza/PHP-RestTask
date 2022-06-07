<?php

declare(strict_types=1);

class MoviesProvider
{
	
	const ENDPOINT_URL = 'http://www.omdbapi.com/';
	
	public function __construct()
	{
		
	}
	
	public function get_movie()
	{
		$data = [
			'apikey' => Config::OMDB_SECRET_KEY,
			'r'      => 'json',
		];
		
		if (Input::get('title', false) !== false)
		{
			$data['t'] = Input::get('title');
		}
		else
		{
			return [
				'success' => false,
				'error' => "Parameter 'title' is required",
			];
		}
		
		if (Input::get('year', false) !== false)
		{
			$data['y'] = Input::get('year');
		}
		
		if (Input::get('plot', false) !== false)
		{
			$plot = Input::get('plot');
			if ($plot == 'short' || $plot == 'full')
			{
				$data['plot'] = $plot;
			}
			else
			{
				return [
					'success' => false,
					'error' => "Parameter 'plot' must only be 'short' or 'full'",
				];
			}
		}
		
		$response = Requests::get(MoviesProvider::ENDPOINT_URL, $data);
		
		if ($response['status_code'] != 200)
		{
			return [
				'success' => false,
				'error' => "API response status code " . $response['status_code'],
			];
		}
		
		$content = json_decode($response['content'], true);
		if (is_null($content))
		{
			return [
				'success' => false,
				'error' => "Response content not valid JSON",
			];
		}
		
		if ($content['Response'] === "False")
		{
			return [
				'success' => false,
				'error'   => "API response: " . $content['Error'],
			];
		}
		
		return [
			'success' => true,
			'response' => $response['content'],
		];
	}	
	
}
