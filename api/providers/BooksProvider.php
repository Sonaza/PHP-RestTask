<?php

declare(strict_types=1);

class BooksProvider
{
	
	const BASE_ENDPOINT_URL = 'https://openlibrary.org';
	
	public function get_book(): array
	{
		$isbn = Input::get('isbn', false);
		if ($isbn === false)
		{
			return [
				'success' => false,
				'error' => "Parameter 'isbn' is required",
			];
		}
		
		$url = BooksProvider::BASE_ENDPOINT_URL . '/isbn/' . $isbn . '.json';
		
		try
		{
			$response = Requests::get_json($url);
		}
		catch (RequestsInvalidJSONException $e)
		{
			return [
				'success' => false,
				'error' => "Response content not valid JSON",
			];
		}
		
		if ($response['status_code'] != 200)
		{
			return [
				'success' => false,
				'error' => "API response status code " . $response['status_code'],
			];
		}
		
		$book_info = $response['content'];
		
		// Replace cover ids with a full URL to the cover image
		if (array_key_exists('covers', $book_info))
		{
			foreach ($book_info['covers'] as &$cover)
			{
				$cover = 'https://covers.openlibrary.org/b/id/' . $cover . '-L.jpg';
			}
		}
		
		// Make subqueries for all authors if applicable
		if (array_key_exists('authors', $book_info))
		{
			foreach ($book_info['authors'] as &$author)
			{
				$author_response = $this->get_additional_information($author['key']);
				if ($author_response['success'] === true)
				{
					$author = $author_response['response'];
					
					// Replace photo ids with a full URL to the author photo
					if (array_key_exists('photos', $author))
					{
						foreach ($author['photos'] as $index => &$photo)
						{
							// Omit invalid photo index
							if ($photo == -1)
							{	
								unset($author['photos'][$index]);
								continue;
							}
							
							$photo = 'https://covers.openlibrary.org/a/id/' . $photo . '-L.jpg';
						}
					}
				}
				else
				{
					return [
						'success' => false,
						'error' => "Failed to retrieve book authors information: " . $author_response['error'],
					];
				}
			}
		}
		
		// Make subqueries for all works if applicable
		if (array_key_exists('works', $book_info))
		{
			foreach ($book_info['works'] as &$work)
			{
				$work_response = $this->get_additional_information($work['key']);
				if ($work_response['success'] === true)
				{
					$work = $work_response['response'];
					
					// Replace cover ids with a full URL to the cover image
					if (array_key_exists('covers', $work))
					{
						foreach ($work['covers'] as $index => &$cover)
						{
							// Omit invalid cover index
							if ($cover == -1)
							{	
								unset($work['covers'][$index]);
								continue;
							}
							
							$cover = 'https://covers.openlibrary.org/b/id/' . $cover . '-L.jpg';
						}
					}
				}
				else
				{
					return [
						'success' => false,
						'error' => "Failed to retrieve book works information: " . $author_response['error'],
					];
				}
			}
		}
		
		return [
			'success'  => true,
			'response' => $book_info,
		];
	}
	
	
	private function get_additional_information(string $information_key): array
	{
		assert(!empty($information_key) && is_string($information_key));
		
		$url = BooksProvider::BASE_ENDPOINT_URL . $information_key . '.json';
		
		try
		{
			$response = Requests::get_json($url);
		}
		catch (RequestsInvalidJSONException $e)
		{
			return [
				'success' => false,
				'error' => "Response content not valid JSON",
			];
		}
		
		if ($response['status_code'] != 200)
		{
			return [
				'success' => false,
				'error' => "API response status code " . $response['status_code'],
			];
		}
		
		return [
			'success'  => true,
			'response' => $response['content'],
		];
	}
	
}
