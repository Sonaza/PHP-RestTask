<?php

declare(strict_types=1);

class MoviesProvider
{
	
	const ENDPOINT_URL = 'http://www.omdbapi.com/';
	
	public function get_movie(): array
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
		
		try
		{
			$response = Requests::get_json(MoviesProvider::ENDPOINT_URL, $data);
			
			if ($response['status_code'] != 200)
			{
				return [
					'success' => false,
					'error' => "API response status code " . $response['status_code'],
				];
			}
		}
		catch (RequestsInvalidJSONException $e)
		{
			return [
				'success' => false,
				'error' => "Response content not valid JSON",
			];
		}
		
		$content = $response['content'];
		if ($content['Response'] === "False")
		{
			return [
				'success' => false,
				'error'   => "API response: " . $content['Error'],
			];
		}
		
		return [
			'success' => true,
			'response' => $content,
		];
	}	
	
}
