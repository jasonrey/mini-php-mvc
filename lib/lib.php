<?php
!defined('SERVER_EXEC') && die('No access.');

$base = dirname(__FILE__);

require_once($base . '/../config.php');
require_once($base . '/constant.php');
require_once($base . '/req.php');

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

		$asset = array_shift($segments);

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

	public static function ajax()
	{
		Lib::load('ajax');

		return Ajax::init();
	}

	public static function db()
	{
		Lib::load('database');

		return Database::init();
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

	/* Libraries loader - END */

	/* Utilities methods - START */

	public static function route()
	{
		 $requesturi = trim(str_replace(Config::getBaseFolder(), '', $_SERVER['REQUEST_URI']), '/');

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
			// Require error
			return true;
		}

		$result = $router->route($segments);

		if ($result === false) {
			// Require error
			return true;
		}

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

	public static function escape($output, $fromUrl = false)
	{
		$string = htmlspecialchars($output, ENT_COMPAT, 'UTF-8');

		if ($fromUrl) {
			$string = urlencode($string);
		}

		return $string;
	}

	public static function encodePassword($password)
	{
		return base64_encode(hash('sha256', $password, true));
	}

	/* Utilities methods - END */
}

// Initiate session first
Lib::session();