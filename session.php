<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Config;

class Session
{
	public static $instance;

	public static $id;

	public static function init()
	{
		session_start();

		self::$id = session_id();
	}

	public static function get($key, $default = null)
	{
		if (!isset($_SESSION[$key])) {
			return $default;
		}

		return $_SESSION[$key];
	}

	public static function set($key, $val)
	{
		$_SESSION[$key] = $val;
	}

	public static function delete($key)
	{
		unset($_SESSION[$key]);
	}

	public static function once($key)
	{
		$value = self::get($key);

		self::delete($key);

		return $value;
	}

	public static function setError($message)
	{
		$key = Config::getKey('error');

		self::set($key, $message);
	}

	public static function getError()
	{
		$key = Config::getKey('error');

		return self::once($key);
	}
}
