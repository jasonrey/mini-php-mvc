<?php namespace Mini\Lib;
!defined('SERVER_EXEC') && die('No access.');

class Router
{
	private static $routes = array(
		'get' => array(),
		'post' => array(),
		'all' => array()
	);

	public static function route()
	{
		$prefix = '/' . Config::getBaseFolder();

		$req = $_SERVER['REQUEST_URI'];

		// $_SERVER['REQUEST_METHOD'];

		if (substr($req, 0, strlen($prefix)) === $prefix) {
			$req = substr($req, strlen($prefix));
		}

		$req = trim($req, '/');

		$reqFragments = explode('?', $req);

		$reqBase = $reqFragments[0];

		$reqSegments = explode('/', $reqBase);

		$getRoutes = self::$routes[strtolower($_SERVER['REQUEST_METHOD'])];

		foreach ($getRoutes as $route) {
			$routeSegments = explode('/', $route);

			$i = 0;

			$matched = false;

			foreach ($routeSegments as $segment) {
				$optional = $segment[0] === '|';

				if ($segment[0] === '|') {
					$segment = substr($segment, 1);
				}

				if ($segment[0] === ':') {
					$segment = substr($segment, 1);
				}

				if ($optional && $segment !== $reqSegments[$i]) {
					continue;
				}

				if ($segment[0] !== '|' &&
					$segment[0] !== ':' &&
					$segment === $reqSegments[$i]) {
					continue;
				}

				if ($segment[0] === ':') {
					$name = substr($segment, 1);
					Req::get($name, $reqSegments[$i]);
				} else {
					if ($segment !== $reqSegments[$i]) {
						break;
					}
				}

				$i++;
			}
		}

		// $segments = explode('/', $reqFragments[0]);

		// if ($segments[0] !== 'index.php') {
		// 	Lib::load('router');

		// 	foreach (Router::getRouters() as $router) {
		// 		if (is_string($router->allowedRoute) && $segments[0] !== $router->allowedRoute) {
		// 			continue;
		// 		}

		// 		if (is_array($router->allowedRoute) && !in_array($segments[0], $router->allowedRoute)) {
		// 			continue;
		// 		}

		// 		$router->decode($segments);
		// 	}
		// }

		// Check for API call
		if (Req::hasget('api')) {
			$apiName = preg_replace('/[-\.]/u', '', Req::get('api'));
			$action = preg_replace('/[-\.]/u', '', Req::get('action'));

			$api = Lib::api($apiName);

			if (!is_callable(array($api, $action))) {
				return Lib::api()->fail();
			}

			return $api->$action();
		}

		// Check for controller
		if (Req::hasget('controller')) {
			$controllerName = preg_replace('/[-\.]/u', '', Req::get('controller'));
			$action = preg_replace('/[-\.]/u', '', Req::get('action'));

			$controller = Lib::controller($controllerName);

			if (!is_callable(array($controller, $action))) {
				return $controller->execute();
			}

			return $controller->$action();
		}

		$viewname = preg_replace('/[-\.]/u', '', Req::get('view'));

		if (empty($viewname)) {
			$viewname = 'index';
		}

		return Lib::view($viewname)->display();
	}

	public static function get($path, $callback)
	{
		return self::addRoute('get', $path, $callback);
	}

	public static function post($path, $callback)
	{
		return self::addRoute('post', $path, $callback);
	}

	public static function all($path, $callback)
	{
		return self::addRoute('all', $path, $callback);
	}

	public static function addRoute($method, $path, $callback)
	{
		self::$routes[$method][] = array(
			'path' => trim($path, '/'),
			'callback' => $callback
		);
	}

	/*public $name;

	public $segments = array();

	public $allowedRoute;
	public $allowedBuild;

	private static $instances = array();

	public static function getRouters()
	{
		static $routers = array();

		if (empty($routers)) {
			foreach (glob(Config::getBasePath() . '/routers/*.php') as $routerFile) {
				$name = basename($routerFile, '.php');

				$routers[] = Lib::router($name);
			}
		}

		return $routers;
	}

	public static function getInstance($name)
	{
		$state = Lib::load('router', $name);

		if (!$state) {
			return false;
		}

		if (!isset(self::$instances[$name])) {
			$classname = ucfirst($name) . 'Router';

			self::$instances[$name] = new $classname;

			self::$instances[$name]->name = $name;
		}

		return self::$instances[$name];
	}

	public function decode($segments)
	{
		foreach ($segments as $index => $value) {
			if (empty($value) || !isset($this->segments[$index])) {
				continue;
			}

			Req::set('GET', $this->segments[$index], $value);
		}
	}

	public function encode($key, &$options, &$segments)
	{
		foreach ($this->segments as $index => $key) {
			if (!isset($options[$key])) {
				continue;
			}

			$segments[] = urlencode($options[$key]);
			unset($options[$key]);
		}
	}*/
}
