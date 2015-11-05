<?php
!defined('SERVER_EXEC') && die('No access.');

class Config
{
	public static $dbenv = 'development';
	public static $dbconfig = array(
		'default' => array(
			'development' => array(
				'server' => 'localhost',
				'username' => 'root',
				'password' => 'base64_encode',
				'database' => ''
			),
			'production' => array(
				'server' => 'localhost',
				'username' => '',
				'password' => 'base64_encode',
				'database' => ''
			)
		)
	);
	public static $env = 'development';
	public static $sef = true;
	public static $base = 'git/mini-php-mvc';
	public static $pagetitle = '';

	// Unique key to identify admin session
	// This key will be hashed to use as cookie key
	// Reset key to force admin log out
	public static $adminkey = 'adminkey';

	public static function getBaseUrl()
	{
		return 'http://' . $_SERVER['SERVER_NAME'];
	}

	public static function getBaseFolder()
	{
		return self::$base;
	}

	public static function getHTMLBase()
	{
		$base = Config::getBaseUrl();
		$folder = Config::getBaseFolder();

		if (!empty($folder)) {
			$base .= '/' . $folder;
		}

		$base .= '/';

		return $base;
	}

	public static function getBasePath()
	{
		return dirname(__FILE__);
	}

	public static function getPageTitle()
	{
		return self::$pagetitle;
	}

	public static function getDBConfig($key = 'default')
	{
		return self::$dbconfig[$key][self::$dbenv];
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

	public static function getAdminKey()
	{
		return hash('sha256', Config::$adminkey);
	}
}
