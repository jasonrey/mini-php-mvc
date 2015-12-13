<?php
!defined('SERVER_EXEC') && die('No access.');

class Lib
{
	/* Main php file loader */
	public static function load($namespace, $asset = null)
	{
		static $loadedBases = array();
		static $loadedAssets = array();

		if (!empty($asset)) {
			$namespace .= '/' . $asset;
		}

		$segments = explode('/', $namespace);

		$basepath = dirname(__FILE__);

		$lib = array_shift($segments);

		if (!isset($loadedBases[$lib])) {
			$basefile = $basepath . '/' . $lib . '.php';

			if (!file_exists($basefile)) {
				return false;
			}

			require_once($basefile);

			$loadedBases[$lib] = true;

			$loadedAssets[$lib] = array();
		}

		$asset = implode('/', $segments);

		if (!empty($asset) && !isset($loadedAssets[$lib][$asset])) {
			$assetfile = $basepath . '/../' . $lib . 's/' . $asset . '.php';

			if (!file_exists($assetfile)) {
				return false;
			}

			require_once($assetfile);

			$loadedAssets[$lib][$asset] = true;
		}

		return true;
	}

	/* Libraries loader - START */

	public static function api($name = null, $options = array())
	{
		Lib::load('api');

		if (empty($name)) {
			$api = new Api;

			$api->config($options);

			return $api;
		}

		return Api::getInstance($name, $options);
	}

	public static function db($key = 'default')
	{
		Lib::load('database');

		return Database::getInstance($key);
	}

	public static function controller($name = null)
	{
		Lib::load('controller');

		return Controller::getInstance($name);
	}

	public static function view($name)
	{
		Lib::load('view', $name);

		$classname = ucfirst($name) . 'View';

		if (!class_exists($classname)) {
			$classname = 'View';
		}

		$view = new $classname;

		$view->viewname = $name;

		return $view;
	}

	public static function model($name = null)
	{
		Lib::load('model');

		return Model::getInstance($name);
	}

	public static function table($name)
	{
		Lib::load('table/' . $name);

		$classname = ucfirst($name) . 'Table';

		$table = new $classname;

		$table->tablename = $name;

		return $table;
	}

	public static function router($name)
	{
		Lib::load('router');

		return Router::getInstance($name);
	}

	public static function session()
	{
		Lib::load('session');

		return Session::init();
	}

	public static function cookie()
	{
		Lib::load('cookie');

		$cookie = Cookie::init();

		$totalArgs = func_num_args();
		$arguments = func_get_args();

		if ($totalArgs === 1) {
			return $cookie->get($arguments[0]);
		}

		if ($totalArgs === 2) {
			if ($arguments[1] === null) {
				return $cookie->delete($arguments[0]);
			}

			return $cookie->set($arguments[0], $arguments[1]);
		}

		return $cookie;
	}

	public static function helper($name)
	{
		Lib::load('helper');

		return call_user_func_array(array('Helper', 'getInstance'), func_get_args());
	}

	public static function file($path, $filename = null)
	{
		Lib::load('file');

		$file = new File($path, $filename);

		return $file;
	}

	/* Libraries loader - END */

	/* Utilities methods - START */

	public static function route()
	{
		$prefix = '/' . Config::getBaseFolder();

		$requesturi = $_SERVER['REQUEST_URI'];

		if (substr($requesturi, 0, strlen($prefix)) == $prefix) {
			$requesturi = substr($requesturi, strlen($prefix));
		}

		$requesturi = trim($requesturi, '/');

		$requestSegments = explode('?', $requesturi);

		$segments = explode('/', $requestSegments[0]);

		if ($segments[0] !== 'index.php') {
			Lib::load('router');

			foreach (Router::getRouters() as $router) {
				if (is_string($router->allowedRoute) && $segments[0] !== $router->allowedRoute) {
					continue;
				}

				if (is_array($router->allowedRoute) && !in_array($segments[0], $router->allowedRoute)) {
					continue;
				}

				$router->decode($segments);
			}
		}

		// Check for API call
		if (Req::hasget('api')) {
			$apiName = Req::get('api');
			$action = Req::get('action');

			$api = Lib::api($apiName);

			if (!is_callable(array($api, $action))) {
				return Lib::api()->fail();
			}

			return $api->$action();
		}

		// Check for controller
		if (Req::hasget('controller')) {
			$controllerName = Req::get('controller');
			$action = Req::get('action');

			$controller = Lib::controller($controllerName);

			if (!is_callable(array($api, $action))) {
				return $controller->execute();
			}

			return $controller->$action();
		}

		$viewname = Req::get('view');

		if (empty($viewname)) {
			$viewname = 'error';
		}

		return Lib::view($viewname)->display();
	}

	public static function url($key, $options = array(), $external = false)
	{
		$values = array();

		$link = $external ? Config::getHTMLBase() : '';

		if (Config::$sef) {
			Lib::load('router');

			$segments = array();

			foreach (Router::getRouters() as $router) {
				if (is_string($router->allowedBuild) && $key !== $router->allowedBuild) {
					continue;
				}

				if (is_array($router->allowedBuild) && !in_array($key, $router->allowedBuild)) {
					continue;
				}

				$router->encode($key, $options, $segments);
			}

			if (!empty($segments)) {
				$link .= implode('/', $segments);
			}
		} else {
			$link .= 'index.php';
		}

		if (!empty($options)) {
			$values = array();

			foreach ($options as $k => $v) {
				$values[] = urlencode($k) . '=' . urlencode($v);
			}

			$queries = implode('&', $values);

			if (!empty($queries)) {
				$queries = '?' . $queries;
			}

			$link .= $queries;
		}

		return $link;
	}

	public static function redirect($target, $options = array(), $absolute = false)
	{
		$url = $absolute ? $target : Lib::url($target, $options, true);

		header('Location: ' . $url);
		die();
	}

	public static function output($namespace, $vars = array())
	{
		$segments = explode('/', $namespace);
		$view = array_shift($segments);
		$path = implode('/', $segments);

		$class = Lib::view($view);

		$class->set($vars);

		return $class->output($path);
	}

	public static function hash($password)
	{
		return hash('sha256', $password);
	}

	/* Utilities methods - END */
}

$base = dirname(__FILE__);

// Load config
require_once($base . '/../config.php');

// Initiate session first
Lib::session();

// Load constant
Lib::load('constant');

// Load additional libraries
Lib::load('req');
