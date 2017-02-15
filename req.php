<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

class Req
{
	public static function isJSON()
	{
		return $_SERVER['CONTENT_TYPE'] === 'application/json';
	}

	public static function hasjson($key, $strict = true)
	{
		if (!self::isJSON()) {
			return false;
		}

		return Req::haskey(json_decode(file_get_contents('php://input'), true), $key, $strict);
	}

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

	private static function process()
	{
		$total = func_num_args();
		$args = func_get_args();

		$method = strtoupper(array_shift($args));

		$data = array();

		switch ($method) {
			case 'GET':
				$data = $_GET;
			break;
			case 'POST':
				$data = $_POST;
			break;
			case 'REQUEST':
				$data = $_REQUEST;
			break;
			case 'JSON':
				$data = self::isJSON() ? json_decode(file_get_contents('php://input'), true) : [];
		}

		if ($total === 1) {
			return Req::returnKey($data);
		} else if ($total === 2) {
			return Req::returnKey($data, $args[0]);
		} else {
			return Req::set($method, $args[0], $args[1]);
		}
	}

	public static function json()
	{
		$args = func_get_args();

		array_unshift($args, 'json');

		return call_user_func_array(array('self', 'process'), $args);
	}

	public static function get()
	{
		$args = func_get_args();

		array_unshift($args, 'get');

		return call_user_func_array(array('self', 'process'), $args);
	}

	public static function post()
	{
		$args = func_get_args();

		array_unshift($args, 'post');

		return call_user_func_array(array('self', 'process'), $args);
	}

	public static function request()
	{
		$args = func_get_args();

		array_unshift($args, 'request');

		return call_user_func_array(array('self', 'process'), $args);
	}

	private static function returnKey($collection, $key = null)
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
			return null;
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
