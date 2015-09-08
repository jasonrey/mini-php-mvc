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

	public static function hasrequest($key)
	{
		return isset($_REQUEST[$key]);
	}

	public static function get($key = null, $default = null)
	{
		if (empty($key)) {
			$data = array();

			foreach ($_GET as $key => $value) {
				$data[$key] = Lib::escape($value);
			}

			return $data;
		}

		return self::hasget($key) ? Lib::escape($_GET[$key]) : $default;
	}

	public static function post($key = null, $default = null)
	{
		if (empty($key)) {
			$data = array();

			foreach ($_POST as $key => $value) {
				$data[$key] = Lib::escape($value);
			}

			return $data;
		}

		return self::haspost($key) ? Lib::escape($_POST[$key]) : $default;
	}

	public static function request($key = null, $default = null)
	{
		if (empty($key)) {
			$data = array();

			foreach ($_REQUEST as $key => $value) {
				$data[$key] = Lib::escape($value);
			}

			return $data;
		}

		return self::hasrequest($key) ? Lib::escape($_REQUEST[$key]) : $default;
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
