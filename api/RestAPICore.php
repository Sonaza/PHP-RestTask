<?php

declare(strict_types=1);

class RestProviderInvalidMethodException extends Exception {}
class RestProviderInvalidNumberOfArgumentsException extends Exception {}

class RestAPICore
{
	const VALID_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
	
	private $registered_routes = [];
	
	public function __construct()
	{
		spl_autoload_register([$this, 'providers_autoload']);
	}
	
	private function providers_autoload(string $class_name)
	{
		if (stripos($class_name, 'Provider') !== false)
		{
			require_once __DIR__ . "/providers/" . $class_name . ".php";
		}
	}
	
	public function route(): array
	{
		$http_method = $_SERVER['REQUEST_METHOD'];
		
		if (!array_key_exists($http_method, $this->registered_routes))
		{
			http_response_code(404);
			return [
				'success' => false,
				'error'   => 'not found',
			];
		}
		
		// print_r($_SERVER);
		// print_r(Helpers::getRequestUri());
		
		$uri_segments = explode('/', Helpers::getRequestUri());
		$route_path = array_shift($uri_segments);
		
		if (!array_key_exists($route_path, $this->registered_routes[$http_method]))
		{
			http_response_code(404);
			return [
				'success' => false,
				'error'   => 'not found',
			];
		}
		
		if (!is_null($this->registered_routes[$http_method][$route_path]['auth_method']))
		{
			$has_authorization = $this->call_provider_method($this->registered_routes[$http_method][$route_path]['auth_method']);
			if (!$has_authorization)
			{
				http_response_code(401);
				return [
					'success' => false,
					'error'   => 'unauthorized',
				];
			}
		}
		
		try
		{
			return $this->call_provider_method($this->registered_routes[$http_method][$route_path]['provider_method'], $uri_segments);
		}
		catch (RestProviderInvalidMethodException $e)
		{
			http_response_code(500);
			return [
				'success' => false,
				'error'   => 'invalid method',
			];
		}
		catch (RestProviderInvalidNumberOfArgumentsException $e)
		{
			http_response_code(400);
			return [
				'success' => false,
				'error'   => 'invalid number of arguments',
			];
		}
		
		http_response_code(500);
		return [
			'success' => false,
			'error'   => 'unknown error',
		];
	}
	
	public function add_endpoint(string $http_method, string $route_path,
	                             string $provider_method, ?string $auth_provider_method = null): bool
	{
		if (!in_array($http_method, RestAPICore::VALID_METHODS))
		{
			return false;
		}
		
		@list($provider_class, $class_method_name) = explode('@', $provider_method, 2);
		
		// Verifying the splitting actually resulted in two parts (not empty)
		if (!$provider_class || !$class_method_name)
		{
			return false;
		}
		
		$this->registered_routes[$http_method][$route_path] = [
			'provider_method'  => $provider_method,
			'auth_method'      => $auth_provider_method,
		];
		
		// print_r($this->registered_routes);
		
		return true;
	}
	
	private function call_provider_method(string $provider_method, array $arguments = [])
	{
		if (empty($provider_method))
		{
			throw new RestProviderInvalidMethodException("Method name empty.");
		}
		
		@list($provider_class, $class_method_name) = explode('@', $provider_method, 2);
		
		// Verifying the splitting actually resulted in two parts (not empty)
		if (empty($provider_class) || empty($class_method_name))
		{
			throw new RestProviderInvalidMethodException("Provider class/method not defined.");
		}
		
		$reflection_method = new ReflectionMethod($provider_class, $class_method_name);
		
		$num_paremeters = count($arguments);
		if ($num_paremeters < $reflection_method->getNumberOfRequiredParameters() ||
			$num_paremeters > $reflection_method->getNumberOfParameters())
		{
			throw new RestProviderInvalidNumberOfArgumentsException("Invalid number of arguments.");
		}
		
		$provider_instance = new $provider_class();
		return call_user_func_array([$provider_instance, $class_method_name], $arguments);
	}
	
	
}
