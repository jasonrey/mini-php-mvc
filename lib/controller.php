<?php
!defined('SERVER_EXEC') && die('No access.');

class Controller
{
	private static $instances = array();

	public $controllername;

	public static function getInstance($name = null)
	{
		if (empty($name)) {
			if (!isset(self::$instances['self'])) {
				self::$instances['self'] = new Controller;
			}

			return self::$instances['self'];
		}

		if (!isset(self::$instances[$name])) {
			Lib::load('controller', $name);

			$classname = ucfirst($name) . 'Controller';

			self::$instances[$name] = new $classname;
		}

		return self::$instances[$name];
	}

	public function execute()
	{
		// Controllers are static links that is not used for API purposes, hence it should always redirect to something after performing internal actions
		Lib::redirect('index');
	}
}
