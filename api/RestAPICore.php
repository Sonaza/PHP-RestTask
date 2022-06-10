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
		
		// Not found if no known registered route supports this HTTP method
		if (!array_key_exists($http_method, $this->registered_routes))
		{
			http_response_code(404);
			return [
				'success' => false,
				'error'   => 'Not found.',
			];
		}
		
		// Retrieve request URI and split it into segments.
		// First part should be the route we're looking for and
		// the rest are arguments that will be given to the provider method.
		$uri_segments = explode('/', Helpers::getRequestUri());
		$route_path = array_shift($uri_segments);
		
		// Verify the path is registered.
		if (!array_key_exists($route_path, $this->registered_routes[$http_method]))
		{
			http_response_code(404);
			return [
				'success' => false,
				'error'   => 'Not found.',
			];
		}
		
		// If an auth method is defined this route will require authorization.
		// Authorization can be received by calling the auth method and if it returns
		// a boolean true the user is allowed to access this endpoint route.
		// Specific implementation depends on the auth provider.
		if (!is_null($this->registered_routes[$http_method][$route_path]['auth_method']))
		{
			try
			{
				$has_authorization = $this->call_provider_method($this->registered_routes[$http_method][$route_path]['auth_method']);
				if (!$has_authorization)
				{
					http_response_code(401);
					return [
						'success' => false,
						'error'   => 'Unauthorized.',
					];
				}
			}
			catch (RestProviderInvalidMethodException $e)
			{
				http_response_code(500);
				return [
					'success' => false,
					'error'   => 'Invalid authorization method.',
				];
			}
			catch (RestProviderInvalidNumberOfArgumentsException $e)
			{
				http_response_code(400);
				return [
					'success' => false,
					'error'   => 'Invalid number of arguments for authorization method.',
				];
			}
		}
		
		// Finally we can try calling the endpoint provider method.
		try
		{
			return $this->call_provider_method($this->registered_routes[$http_method][$route_path]['provider_method'], $uri_segments);
		}
		catch (RestProviderInvalidMethodException $e)
		{
			http_response_code(500);
			return [
				'success' => false,
				'error'   => 'Invalid method.',
			];
		}
		catch (RestProviderInvalidNumberOfArgumentsException $e)
		{
			http_response_code(400);
			return [
				'success' => false,
				'error'   => 'Invalid number of arguments.',
			];
		}
		
		// Something has gone horribly wrong and normally this should not happen.
		http_response_code(500);
		return [
			'success' => false,
			'error'   => 'Unknown error.',
		];
	}
	
	public function add_endpoint(string $http_method, string $route_path,
	                             string $provider_method, ?string $auth_provider_method = null): bool
	{
		if (!in_array($http_method, RestAPICore::VALID_METHODS))
			return false;
		
		// Verify the method string is valid (with class and method name)
		if (!$this->is_valid_method_string($provider_method, $provider_class, $class_method_name))
			return false;
		
		// No duplicate endpoint paths allowed
		if (array_key_exists($http_method, $this->registered_routes) && array_key_exists($route_path, $this->registered_routes[$http_method]))
			return false;
		
		$this->registered_routes[$http_method][$route_path] = [
			'provider_method'  => $provider_method,
			'auth_method'      => $auth_provider_method,
		];
		
		return true;
	}
	
	private function is_valid_method_string(string $method_string, ?string &$class_name = null, ?string &$method_name = null)
	{
		$matches = [];
		
		// Match a method string in the format <class_name>@<class_method_name>
		if (!preg_match('/([\w]*)@([\w]*)/', $method_string, $matches))
			return false;
		
		// Passing the matched capture groups if requested by caller
		if (!is_null($class_name) && !is_null($method_name))
		{
			$class_name = $matches[1];
			$method_name = $matches[2];
		}
		
		return true;
	}
	
	private function call_provider_method(string $provider_method, array $arguments = [])
	{
		$provider_class = '';
		$class_method_name = '';
		
		// Verify the method string is valid (with class and method name)
		if (!$this->is_valid_method_string($provider_method, $provider_class, $class_method_name))
		{
			throw new RestProviderInvalidMethodException("Provider class/method not defined.");
		}
		
		// Use reflection to retrieve method parameter count from the class
		$reflection_method = new ReflectionMethod($provider_class, $class_method_name);
		$num_paremeters = count($arguments);
		if ($num_paremeters < $reflection_method->getNumberOfRequiredParameters() ||
			$num_paremeters > $reflection_method->getNumberOfParameters())
		{
			throw new RestProviderInvalidNumberOfArgumentsException("Invalid number of arguments.");
		}
		
		// Create a new instance of the class and call the method
		$provider_instance = new $provider_class();
		return call_user_func_array([$provider_instance, $class_method_name], $arguments);
	}
	
	
}
