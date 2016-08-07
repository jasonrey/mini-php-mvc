<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Config;

class Cookie
{
	public static function get($key, $default = null)
	{
		if (!isset($_COOKIE[$key])) {
			return $default;
		}

		return $_COOKIE[$key];
	}

	public static function has($key)
	{
		return isset($_COOKIE[$key]);
	}

	public static function set($key, $value)
	{
		setcookie($key, $value, time() + 60 * 60 * 24 * 500, '/' . \Mini\Config::getBaseFolder(), '', false, true);
	}

	public static function delete($key)
	{
		unset($_COOKIE[$key]);

		setcookie($key, null, time() - 60 * 60 * 24, '/' . \Mini\Config::getBaseFolder(), '', false, true);
	}

	public static function getIdentifier($salt)
	{
		$key = Config::getKey($salt);

		return self::get($key);
	}

	public static function setIdentifier($salt, $value)
	{
		$key = Config::getKey($salt);

		return self::set($key, $value);
	}

	public static function deleteIdentifier($salt)
	{
		$key = Config::getKey($salt);

		unset($_COOKIE[$key]);

		setcookie($key, null, time() - 60 * 60 * 24, '/' . \Mini\Config::getBaseFolder(), '', false, true);
	}
}