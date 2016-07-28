<?php namespace Mini\Lib;
!defined('SERVER_EXEC') && die('No access.');

class Cookie
{
	public static $instance;

	public static function init()
	{
		if (empty(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function get($key, $default = null)
	{
		if (!isset($_COOKIE[$key])) {
			return $default;
		}

		return $_COOKIE[$key];
	}

	public function set($key, $value)
	{
		setcookie($key, $value, time()+60*60*24*500, '/' . \Mini\Config::getBaseFolder(), '', false, true);
	}

	public function delete($key)
	{
		unset($_COOKIE[$key]);

		setcookie($key, null, time()-60*60*24, '/' . \Mini\Config::getBaseFolder(), '', false, true);
	}
}
