<?php
!defined('SERVER_EXEC') && die('No access.');

class Req
{
	public static function hasget($key)
	{
		return isset($_GET[$key]);
	}

	public static function haspost($key)
	{
		return isset($_POST[$key]);
	}

	public static function get($key, $default = null)
	{
		return self::hasget($key) ? Lib::escape($_GET[$key]) : $default;
	}

	public static function post($key, $default = null)
	{
		return self::haspost($key) ? Lib::escape($_POST[$key]) : $default;
	}

	public static function set($type, $key, $value)
	{
		switch (strtolower($type)) {
			case 'get':
				$_GET[$key] = $value;
			break;

			case 'post':
				$_POST[$key] = $value;
			break;
		}
	}
}