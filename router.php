<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

use Mini\Lib;
use Mini\Config;

class Router
{
	private static $routes = array(
		'get' => array(),
		'post' => array(),
		'all' => array()
	);

	private static $builds = array();

	private static function loadRoutes()
	{
		static $loaded;

		if (empty($loaded)) {
			foreach (glob(Lib::path('routers/*.php')) as $routerFile) {
				require $routerFile;
			}

			$loaded = true;
		}
	}

	public static function route()
	{
		self::loadRoutes();

		if (Config::$sef) {

			$prefix = '/' . Config::getBaseFolder();

			$req = $_SERVER['REQUEST_URI'];

			if (substr($req, 0, strlen($prefix)) === $prefix) {
				$req = substr($req, strlen($prefix));
			}

			$req = trim($req, '/');

			$reqFragments = explode('?', $req);

			$reqBase = $reqFragments[0];

			$reqSegments = explode('/', $reqBase);

			foreach (array(strtolower($_SERVER['REQUEST_METHOD']), 'all') as $method) {
				foreach (self::$routes[$method] as $route) {
					$i = 0;

					$matched = true;

					$params = array();

					foreach (explode('/', $route['path']) as $segment) {
						if (substr($segment, 0, 1) !== '|' && substr($segment, 0, 1) !== ':') {
							if (!isset($reqSegments[$i])|| $segment !== $reqSegments[$i]) {
								$matched = false;
								break;
							}
						} else {
							if ($segment[0] === ':') {
								if (empty($reqSegments[$i])) {
									$matched = false;
									break;
								}

								$segment = substr($segment, 1);

								$params[$segment] = $reqSegments[$i];
							}

							if ($segment[0] === '|') {
								// No more req segments
								if (!isset($reqSegments[$i])) {
									break;
								}

								$segment = substr($segment, 1);

								if ($segment[0] === ':') {
									$segment = substr($segment, 1);
									$params[$segment] = $reqSegments[$i];
								} else {
									// Optional unmatch, move to the next without i++
									if ($reqSegments[$i] !== $segment) {
										continue;
									}
								}
							}
						}

						$i++;
					}

					if ($matched) {
						foreach ($params as $getKey => $param) {
							Req::get($getKey, $param);
						}

						if (is_callable($route['callback'])) {
							$route['callback']($params);
						}
					}
				}
			}
		}

		// Check for API call
		if (Req::hasget('api')) {
			$name = preg_replace('/[-\.]/u', '', Req::get('api'));
			$action = preg_replace('/[-\.]/u', '', Req::get('action'));

			$api = '\\Mini\\Api\\' . $name;

			if (is_callable(array($api, $action))) {
				$response = $api::$action();
			} else {
				$response = Lib\Api::fail('Error: No such API.');
			}

			if (is_object($response) || is_array($response)) {
				$response = json_encode($response);
			}

			header('Content-Type: application/json');
			echo $response;

			exit();
		}

		// Check for controller
		if (Req::hasget('controller')) {
			$name = preg_replace('/[-\.]/u', '', Req::get('controller'));
			$action = preg_replace('/[-\.]/u', '', Req::get('action'));

			$controller = '\\Mini\\Controller\\' . $name;

			if (!is_callable(array($controller, $action))) {
				return $controller::execute();
			}

			return $controller::$action();
		}

		$viewname = preg_replace('/[-\.]/u', '', Req::get('view'));

		$classname = '\\Mini\\View\\' . $viewname;

		if (empty($viewname) || !class_exists($classname)) {
			// 404
			return \Mini\View\Error::display();
		}

		return $classname::display();
	}

	public static function get($path, $callback = null)
	{
		return self::addHandler('get', $path, $callback);
	}

	public static function post($path, $callback = null)
	{
		return self::addHandler('post', $path, $callback);
	}

	public static function all($path, $callback = null)
	{
		return self::addHandler('all', $path, $callback);
	}

	public static function addHandler($method, $path, $callback = null)
	{
		self::$routes[$method][] = array(
			'path' => trim($path, '/'),
			'callback' => $callback
		);
	}

	public static function build($path, $callback = null)
	{
		if (is_string($path)) {
			self::$builds[] = array(
				'path' => $path,
				'callback' => $callback
			);
		}

		if (is_callable($path)) {
			self::$builds[] = array(
				'path' => '*',
				'callback' => $path
			);
		}
	}

	public static function encode($data = array())
	{
		foreach (self::$builds as $build) {
			$result = false;

			if ($build['path'] === '*') {
				$result = $build['callback']($data);
			} else if ($build['path'] === '/' || $build['path'] === '') {
				if (empty($data) && is_callable($build['callback'])) {
					$result = $build['callback'](array());
				}
			} else {
				$path = trim($build['path'], '/');

				$segments = explode('/', $path);

				$resultSegments = array();

				$matched = true;

				$usedKeys = array();

				foreach ($segments as $segment) {
					if ($segment[0] === ':' || substr($segment, 0, 2) === '|:') {
						$parts = explode('=', substr($segment, $segment[0] === ':' ? 1 : 2));
						$key = $parts[0];

						if (isset($data[$key]) && (!isset($parts[1]) || (isset($parts[1]) && $data[$key] == $parts[1]))) {
							$resultSegments[] = $data[$key];
							$usedKeys[$key] = $data[$key];
						} else {
							if ($segment[0] === ':') {
								$matched = false;
								break;
							}
						}
					} else {
						$resultSegments[] = $segment;
					}
				}

				// If all segment match
				if ($matched) {
					if (is_callable($build['callback'])) {
						$result = $build['callback']($data);

						if ($result !== false && !empty($data)) {
							$queryString = http_build_query($data);

							if (!empty($queryString)) {
								$result .= '?' . $queryString;
							}
						}
					} else {
						$result = implode('/', $resultSegments);

						$remaining = array_diff_key($data, $usedKeys);

						if (count($remaining) > 0) {
							$queryString = http_build_query($remaining);

							if (!empty($queryString)) {
								$result .= '?' . $queryString;
							}
						}
					}
				}
			}

			// As long as there is 1 route matched, we process it and return

			// However, if $result is explicitly false from $build['callback'] or unchanged from initial value, then we continue
			if ($result === false) {
				continue;
			}

			return is_string($result) ? $result : '';
		}

		// No routes matched

		$result = '';

		$queryString = http_build_query($data);

		if (!empty($queryString)) {
			$result .= '?' . $queryString;
		}

		return $result;
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
