<?php
!defined('SERVER_EXEC') && die('No access.');

class Config
{
	private static $version = '2.0.0';

	public static $sef = true;

	// Define all possible connecting host and the environment to use for it
	public static $baseurl = array(
		'localhost' => 'development'
	);

	// Define the subpaths by environment
	public static $base = array(
		'development' => 'git/mini-php-mvc'
	);

	// Define all possible database host by database key name and environment
	// v2.0
	// Added 'engine' key with possible values of mysql (and mssql, sqlite in the future)
	// Updated config keys with shorthand parameter
	public static $dbconfig = array(
		'default' => array(
			// 'development' => array(
			// 	'engine' => 'mysql',
			// 	'host' => 'localhost',
			// 	'un' => 'root',
			// 	'pw' => 'password',
			// 	'db' => 'a'
			// ),
			'development' => array(
				'server' => '127.0.0.1',
				'username' => 'root',
				'password' => 'cGFzc3dvcmQ=',
				'database' => 'a'
			),
			'production' => array(
				'engine' => 'mysql',
				'host' => 'localhost',
				'un' => '',
				'pw' => '',
				'db' => ''
			)
		),
		'new' => array(
			'development' => array(
				'engine' => 'mysql',
				'host' => 'localhost',
				'un' => 'root',
				'pw' => 'password',
				'db' => 'a'
			)
		)
	);

	public static $pagetitle = '';

	// Unique key to identify admin session
	// This key will be hashed to use as cookie key, literal English string will do
	// Reset key to force all admin log out
	public static $adminkey = 'adminkey';

	// Unique key to identify user session
	// This key will be hashed to use as cookie key, literal English string will do
	// Reset key to force all user log out
	public static $userkey = 'userkey';

	public static function getBaseUrl()
	{
		if (in_array($_SERVER['HTTP_HOST'], array_keys(Config::$baseurl))) {
			return '//' . $_SERVER['HTTP_HOST'];
		}

		return '';
	}

	public static function getBaseFolder()
	{
		return Config::$base[Config::env(false)];
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
		return self::$dbconfig[$key][Config::env(false)];
	}

	public static function env($checkget = true)
	{
		if ($checkget && Req::hasget('environment')) {
			return Req::get('environment');
		}

		$serverName = $_SERVER['HTTP_HOST'];

		return isset(Config::$baseurl[$serverName]) ? Config::$baseurl[$serverName] : 'production';
	}

	public static function getAdminKey()
	{
		return hash('sha256', Config::$adminkey);
	}
}
