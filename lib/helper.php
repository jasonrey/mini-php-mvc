<?php
!defined('SERVER_EXEC') && die('No access.');

class Helper
{
	private static $instances = array();

	public static function getInstance($name)
	{
		if (!isset(self::$instances[$name])) {
			Lib::load('helper', $name);

			$classname = ucfirst($name) . 'Helper';

			$class = new $classname;

			if (method_exists($class, 'init')) {
				$args = func_get_args();
				array_shift($args);
				call_user_func_array(array($class, 'init'), $args);
			}

			self::$instances[$name] = $class;
		}

		return self::$instances[$name];
	}
}