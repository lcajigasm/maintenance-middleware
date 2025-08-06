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

		// Si el modo de mantenimiento está activado globalmente
		if($this->maintenanceMode)
		{
			$response
			->withHeader('Content-type', 'application/json')
			->getBody()
			->write(json_encode($this->errorObject));
			return $response;
		}

		// Si hay páginas específicas en mantenimiento, verificar si la ruta actual está en la lista
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
	 * Verifica si la página actual está en la lista de páginas en mantenimiento
	 * @param string $currentPath La ruta actual de la request
	 * @return bool
	 */
	protected function isPageInMaintenance($currentPath)
	{
		foreach($this->specificPages as $page)
		{
			// Normalizar las rutas removiendo barras finales
			$normalizedCurrentPath = rtrim($currentPath, '/');
			$normalizedPage = rtrim($page, '/');
			
			// Si están vacías ambas, es la página de inicio
			if(empty($normalizedCurrentPath) && empty($normalizedPage))
			{
				return true;
			}
			
			// Comparación exacta
			if($normalizedCurrentPath === $normalizedPage)
			{
				return true;
			}
			
			// Verificar si es un patrón con wildcard
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