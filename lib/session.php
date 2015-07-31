<?php
!defined('SERVER_EXEC') && die('No access.');

class Session
{
	public static $instance;

	public $id;

	public static function init()
	{
		if (empty(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct()
	{
		session_start();

		$this->id = session_id();
	}

	public function get($key, $default = null)
	{
		if (!isset($_SESSION[$key])) {
			return $default;
		}

		return $_SESSION[$key];
	}

	public function set($key, $val)
	{
		$_SESSION[$key] = $val;
	}
}