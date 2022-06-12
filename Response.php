<?php

declare(strict_types=1);

class Response
{
	const STATUS_OK           = 200;
	const STATUS_BAD_REQUEST  = 400;
	const STATUS_UNAUTHORIZED = 401;
	const STATUS_NOT_FOUND    = 404;
	const STATUS_SERVER_ERROR = 500;
	
	const CONTENT_TYPE_TEXT = 'text/plain';
	const CONTENT_TYPE_HTML = 'text/html';
	const CONTENT_TYPE_JSON = 'application/json';
	
	private $status_code;
	private $content_type;
	private $content_data;
	
	public function __construct(int $status_code, string $content_type, $content_data)
	{
		$this->status_code = $status_code;
		$this->content_type = $content_type;
		$this->content_data = $content_data;
	}
	
	public function output()
	{
		http_response_code($this->status_code);
		
		header("Content-type: " . $this->content_type);
		switch ($this->content_type)
		{
			case Response::CONTENT_TYPE_JSON:
			{
				if (is_array($this->content_data))
				{
					echo json_encode($this->content_data);
				}
				else
				{
					echo $this->content_data;
				}
			}
			break;
			
			default:
			{
				echo $this->content_data;
			}
			break;	
		}
	}
	
}
