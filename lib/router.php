<?php
!defined('SERVER_EXEC') && die('No access.');

class Router
{
	public $name;

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
	}
}
