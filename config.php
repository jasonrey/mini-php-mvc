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
}