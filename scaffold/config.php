<?php namespace Mini;
!defined('MINI_EXEC') && die('No access.');

class Config
{
	private static $libversion = '2.0.0';

	public static $sef = true;

	// Define all possible connecting host and the environment to use for it
	public static $baseurl = array(
		'localhost' => 'development'
	);

	// Define the subpaths by environment
	public static $base = array(
		'development' => 'mini/test'
	);

	// Define all possible database host by database key name and environment
	// v2.0
	// Added 'engine' key with possible values of mysql (and mssql, sqlite in the future)
	// Updated config keys with shorthand parameter
	public static $dbconfig = array(
		'default' => array(
			'development' => array(
				'engine' => 'mysql',
				'host' => 'localhost',
				'un' => 'root',
				'pw' => 'password',
				'db' => 'mini'
			),
			'production' => array(
				'engine' => 'mysql',
				'host' => 'localhost',
				'un' => '',
				'pw' => '',
				'db' => ''
			)
		)
	);

	public static $pagetitle = '';

	// Manually set if basepath cannot be resolved
	// Else, entry.php will set the basepath to dirname(dirname($_SERVER['SCRIPT_FILENAME']))
	public static $basepath = '';

	// Unique key to identify this project
	// Used as combination string for cookie and session key
	// TODO: Key generator through link
	public static $uniquekey = 'uniquekey';

	// View renderer
	// Empty for default
	// Available values: pug
	public static $viewRenderer = 'v2';

	// CSS renderer
	// Empty for default
	// Available values: less, sass, scss
	public static $cssRenderer = 'sass';

	// v2.0 - Deprecated in favor of Url
	public static function getBaseUrl()
	{
		return Lib\Url::host();
	}

	// v2.0 - Deprecated in favor of Url
	public static function getBaseFolder()
	{
		return Lib\Url::subpath();
	}

	// v2.0 - Deprecated in favor of Url
	public static function getHTMLBase()
	{
		return Lib\Url::base();
	}

	// v2.0 - Deprecated in favor of Path
	public static function getBasePath()
	{
		return Lib\Path::base();
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
		if ($checkget && Lib\Req::hasget('environment')) {
			return Lib\Req::get('environment');
		}

		$serverName = $_SERVER['HTTP_HOST'];

		return isset(Config::$baseurl[$serverName]) ? Config::$baseurl[$serverName] : 'production';
	}

	public static function getKey($salt)
	{
		return hash('sha256', Config::$uniquekey . '-' . $salt);
	}
}
