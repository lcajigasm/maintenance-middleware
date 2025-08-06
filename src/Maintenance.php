<?php
namespace Luisinder\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

Class Maintenance
{
	protected $maintenanceMode;
	protected $errorObject;
	protected $specificPages;

	public function __construct($maintenanceMode, $errorObject, $specificPages = [])
	{
		$this->maintenanceMode = $maintenanceMode;
		$this->errorObject = $errorObject;
		$this->specificPages = is_array($specificPages) ? $specificPages : [$specificPages];
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		$uri = $request->getUri();
		$path = $uri->getPath();

		// If global maintenance mode is enabled
		if($this->maintenanceMode)
		{
			$response
			->withHeader('Content-type', 'application/json')
			->getBody()
			->write(json_encode($this->errorObject));
			return $response;
		}

		// If there are specific pages in maintenance mode, check if current route is in the list
		if(!empty($this->specificPages) && $this->isPageInMaintenance($path))
		{
			$response
			->withHeader('Content-type', 'application/json')
			->getBody()
			->write(json_encode($this->errorObject));
			return $response;
		}

		return $response = $next($request, $response);
	}

	/**
	 * Checks if the current page is in the maintenance pages list
	 * @param string $currentPath The current request path
	 * @return bool
	 */
	protected function isPageInMaintenance($currentPath)
	{
		foreach($this->specificPages as $page)
		{
			// Normalize routes by removing trailing slashes
			$normalizedCurrentPath = rtrim($currentPath, '/');
			$normalizedPage = rtrim($page, '/');
			
			// If both are empty, it's the home page
			if(empty($normalizedCurrentPath) && empty($normalizedPage))
			{
				return true;
			}
			
			// Exact comparison
			if($normalizedCurrentPath === $normalizedPage)
			{
				return true;
			}
			
			// Check if it's a wildcard pattern
			if(strpos($page, '*') !== false)
			{
				$pattern = str_replace('*', '.*', preg_quote($page, '/'));
				if(preg_match('/^' . $pattern . '$/', $currentPath))
				{
					return true;
				}
			}
		}
		
		return false;
	}
}