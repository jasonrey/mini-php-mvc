<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Config;

class Path
{
	public static function base()
	{
		return Config::$basepath;
	}

	public static function resolve($subpath)
	{
		return Path::base() . '/' . $subpath;
	}
}
