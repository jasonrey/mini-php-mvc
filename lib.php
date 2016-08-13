<?php namespace Mini;
!defined('MINI_EXEC') && die('No access.');

class Lib
{
	public static function init($base)
	{
		require $base . '/vendor/autoload.php';

		spl_autoload_register(function($class) use ($base) {
			$segs = explode('\\', $class);

			if ($segs[0] !== 'Mini') {
				return;
			}

			$total = count($segs);

			$file = '';

			if ($segs[1] === 'Lib') {
				if ($total === 3) {
					$file = $base . '/lib/' . strtolower($segs[2]) . '.php';
				}

				if ($total === 4) {
					switch ($segs[2]) {
						case 'DatabaseAdapter':
							$file = $base . '/lib/database-adapters/' . strtolower($segs[3]) . '.php';
						break;
						case 'ViewRenderer':
							$file = $base . '/lib/view-renderers/' . strtolower($segs[3]) . '.php';
						break;
					}
				}
			} else if ($segs[1] === 'Config') {
				$file = $base . '/config.php';
			} else {
				if ($total === 3) {
					$file = $base . '/' . strtolower($segs[1] . 's/' . $segs[2]) . '.php';
				}
			}

			if (file_exists($file)) {
				require $file;
			}
		});

		// Set basepath

		if (empty(Config::$basepath)) {
			Config::$basepath = $base;
		}

		// Initiate session
		Lib\Session::init();

		// Load constant
		require Lib::path('constant.php');

		if (Config::env() === 'development') {
			putenv('PATH=' . getenv('PATH') . ':' . Lib::path('node_modules/.bin'));
		}

		// Initiate route
		Lib\Router::route();
	}

	// v2.0 - Deprecated
	// Loader now uses splLoad instead
	// Main php file loader
	public static function load($namespace, $asset = null)
	{
		static $loadedBases = array(
			'lib' => true
		);
		static $loadedAssets = array(
			'lib' => array()
		);

		if (!empty($asset)) {
			$namespace .= '/' . $asset;
		}

		$segments = explode('/', $namespace);

		$basepath = dirname(__FILE__);

		$lib = array_shift($segments);

		if (!isset($loadedBases[$lib])) {
			Lib::load('lib', $lib);

			$loadedBases[$lib] = true;
			$loadedAssets[$lib] = array();
		}

		$asset = implode('/', $segments);

		if (!empty($asset) && !isset($loadedAssets[$lib][$asset])) {
			$assetfile = $basepath . '/../' . $lib . 's/' . $asset . '.php';

			if ($lib === 'lib') {
				$assetfile = $basepath . '/' . $asset . '.php';
			}

			if (!file_exists($assetfile)) {
				return false;
			}

			require_once($assetfile);

			$loadedAssets[$lib][$asset] = true;
		}

		return true;
	}

	/* Utilities methods - START */

	public static function url($options = array(), $external = false)
	{
		$values = array();

		$link = $external ? Config::getHTMLBase() : '';

		if (Lib\Req::hasget('environment')) {
			$options['environment'] = Req::get('environment');
		}

		if (Config::$sef) {
			$link .= Lib\Router::encode($options);
		} else {
			$link .= 'index.php';

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
		}

		return $link;
	}

	public static function redirect($options = array(), $absolute = false)
	{
		$url = $absolute ? $options : Lib::url($options, true);

		header('Location: ' . $url);
		die();
	}

	public static function output($namespace, $vars = array())
	{
		$segments = explode('/', $namespace);
		$view = array_shift($segments);
		$path = implode('/', $segments);

		$viewclass = '\\Mini\\View\\' . $view;

		$view = new $viewclass();

		$view->set($vars);

		return $view->output($path);
	}

	public static function hash($password)
	{
		return hash('sha256', $password);
	}

	public static function escape($string)
	{
		return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}

	public static function path($subpath)
	{
		return Config::getBasePath() . '/' . $subpath;
	}

	/* Utilities methods - END */
}
