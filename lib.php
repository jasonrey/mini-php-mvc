<?php namespace Mini;
!defined('MINI_EXEC') && die('No access.');

// v2.0 - Deprecated
class Lib
{
	public static function url($options = array(), $external = false)
	{
		return Lib\Url::build($options, $external);
	}

	public static function redirect($options = array(), $absolute = false)
	{
		return Lib\Url::redirect($options, $external);
	}

	public static function hash($string)
	{
		return Lib\String::hash($string);
	}

	public static function escape($string)
	{
		return Lib\String::escape($string);
	}

	public static function path($subpath)
	{
		return Lib\Path::resolve($subpath);
	}
}
