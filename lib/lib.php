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

		return Cookie::init();
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

		if (empty($requesturi)) {
			$requesturi = 'index';
		}

		$requestSegments = explode('?', $requesturi);

		$base = $requestSegments[0];

		if (empty($base)) {
			$base = 'index';
		}

		$segments = explode('/', $base);

		$key = strtolower(array_shift($segments));

		if ($key === 'index.php') {
			$key = 'index';
		}

		$router = Lib::router($key);

		if ($router === false) {
			Lib::view('error')->display();
			return true;
		}

		$result = $router->route($segments);

		return true;
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

	public static function url($target, $options = array(), $external = false)
	{
		$values = array();

		$router = Config::$sef ? Lib::router($target) : false;

		$base = $external ? Config::getBaseUrl() . '/' . Config::getBaseFolder() . '/' : '';

		if (!$router) {
			foreach ($options as $k => $v) {
				$values[] = $k . '=' . $v;
			}

			$queries = implode('&', $values);

			if (!empty($queries)) {
				$queries = '?' . $queries;
			}

			return $base . $target . '.php' . $queries;
		}

		$link = $base . $router->build($options);

		return $link;
	}

	public static function redirect($target, $options = array(), $absolute = false)
	{
		$url = $absolute ? $target : Lib::url($target, $options, true);

		header('Location: ' . $url);
		die();
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
