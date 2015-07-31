<?php
!defined('SERVER_EXEC') && die('No access.');

class Config
{
	public static $dbenv = 'development';
	public static $dbconfig = array(
		'development' => array(
			'server' => 'localhost',
			'username' => 'root',
			'password' => 'base64encode+sha256',
			'database' => ''
		),
		'production' => array(
			'server' => 'localhost',
			'username' => '',
			'password' => 'base64encode+sha256',
			'database' => ''
		)
	);
	public static $env = 'production';
	public static $sef = true;
	public static $base = '';
	public static $pagetitle = '';

	public static function getBaseUrl()
	{
		return 'http://' . $_SERVER['SERVER_NAME'];
	}

	public static function getBaseFolder()
	{
		return self::$base;
	}

	public static function getPageTitle()
	{
		return self::$pagetitle;
	}

	public static function getDBConfig()
	{
		return self::$dbconfig[self::$dbenv];
	}

	public static function env()
	{
		if (Req::hasget('development')) {
			Lib::cookie()->set('development', Req::get('development'));
		}

		if (Lib::cookie()->get('development')) {
			return 'development';
		}

		return self::$env;
	}

	public static function getAdminConfig()
	{
		return array(
			'admin' => 'jGl25bVBBBW96Qi9Te4V37Fnqchz/Eu4qB9vKrRIqRg='
		);
	}
}