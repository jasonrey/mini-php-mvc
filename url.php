<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Config;

class Url
{
	public static function protocol()
	{
		return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
	}

	public static function host()
	{
		if (in_array($_SERVER['HTTP_HOST'], array_keys(Config::$baseurl))) {
			return $_SERVER['HTTP_HOST'];
		}

		return '';
	}

	public static function subpath()
	{
		return Config::$base[Config::env(false)];
	}

	public static function base()
	{
		$protocol = Url::protocol();
		$host = Url::host();
		$folder = Url::subpath();

		$base = $protocol . '://' . $host;

		if (!empty($folder)) {
			$base .= '/' . $folder;
		}

		$base .= '/';

		return $base;
	}

	public static function build($options = array(), $external = false)
	{
		$values = array();

		$link = $external ? Url::base() : '';

		if (Req::hasget('environment')) {
			$options['environment'] = Req::get('environment');
		}

		if (Config::$sef) {
			$link .= Router::encode($options);
		} else {
			$link .= 'index.php';

			if (!empty($options)) {
				$values = array();

				foreach ($options as $k => $v) {
					$values[] = urlencode($k) . '=' . urlencode($v);
				}

				$queries = implode('&', $values);

				if (!empty($queries)) {
					$queries = '?' . $queries;
				}

				$link .= $queries;
			}
		}

		return $link;
	}

	public static function redirect($options = array(), $absolute = false)
	{
		$url = $absolute ? $options : Router::url($options, true);

		header('Location: ' . $url);
		die();
	}
}
