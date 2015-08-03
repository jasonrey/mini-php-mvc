<?php
!defined('SERVER_EXEC') && die('No access.');

class Config
{
	public static $dbenv = 'development';
	public static $dbconfig = array(
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
	);
	public static $env = 'production';
	public static $sef = true;
	public static $base = '';
	public static $pagetitle = '';

	// Unique key to identify admin session
	// This key will be hashed to use as cookie key
	// Reset key to force admin log out
	public static $adminkey = 'adminkey';

	public static $adminconfig = array(
		'admin' => 'sha256'
	);

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

	public static function getAdminConfig($key = null)
	{
		if (!isset($key)) {
			return self::$adminconfig;
		}

		if (!isset(self::$adminconfig[$key])) {
			return;
		}

		return self::$adminconfig[$key];
	}
}
