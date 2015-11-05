<?php
!defined('SERVER_EXEC') && die('No access.');

class Req
{
	public static function hasget($key, $strict = true)
	{
		return Req::haskey($_GET, $key, $strict);
	}

	public static function haspost($key, $strict = true)
	{
		return Req::haskey($_POST, $key, $strict);
	}

	public static function hasrequest($key, $strict = true)
	{
		return Req::haskey($_REQUEST, $key, $strict);
	}

	public static function hasfile($key, $strict = true)
	{
		return Req::haskey($_FILES, $key, $strict);
	}

	private static function haskey($collection, $key, $strict = true)
	{
		if (is_string($key)) {
			return isset($collection[$key]);
		}

		if (is_array($key)) {
			return Req::arrayInArray($key, array_keys($collection), $strict);
		}

		return false;
	}

	private static function arrayInArray($needles, $haystack, $strict = true)
	{
		foreach ($needles as $n) {
			if (in_array($n, $haystack)) {
				if (!$strict) {
					return true;
				}
			} else {
				if ($strict) {
					return false;
				}
			}
		}

		return true;
	}

	public static function get($key = null, $default = null)
	{
		return Req::returnKey($_GET, $key, $default);
	}

	public static function post($key = null, $default = null)
	{
		return Req::returnKey($_POST, $key, $default);
	}

	public static function request($key = null, $default = null)
	{
		return Req::returnKey($_REQUEST, $key, $default);
	}

	private static function returnKey($collection, $key = null, $default = null)
	{
		if (empty($key) || is_array($key)) {
			$data = array();

			foreach ($collection as $k => $v) {
				if (!empty($key) && !in_array($k, $key)) {
					continue;
				}

				$data[$k] = $v;
			}

			return $data;
		}

		if (!isset($collection[$key])) {
			return $default;
		}

		return $collection[$key];
	}

	public static function file($key = null)
	{
		if (empty($key)) {
			return $_FILES;
		}

		return isset($_FILES[$key]) ? $_FILES[$key] : false;
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

			case 'request':
				$_REQUEST[$key] = $value;
			break;
		}
	}
}
