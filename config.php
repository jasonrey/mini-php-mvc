<?php
!defined('SERVER_EXEC') && die('No access.');

class Config
{
	public static function getBaseUrl()
	{
		return 'http://' . $_SERVER['SERVER_NAME'];
	}

	public static function getBaseFolder()
	{
		return '';
	}

	public static function getDBConfig()
	{

	}

	public static function env()
	{
		// 'development'
		// 'production'

		if (Req::get('debug', 0) || Req::get('development', 0) || Lib::cookie()->get('development')) {
			return 'development';
		}

		return 'production';
	}
}