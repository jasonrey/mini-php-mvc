<?php namespace Mini\Api;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib;
use \Mini\Config;
use \Mini\Table;

class Admin extends \Mini\Lib\Api
{
	public static function login()
	{
		$keys = array('username', 'password');

		if (!Lib\Req::haspost($keys)) {
			return self::fail();
		}

		$post = Lib\Req::post($keys);
		extract($post, EXTR_SKIP);

		$admin = Table\Admin::get(array('username' => $username));

		if (!$admin->error || !$admin->checkPassword($password)) {
			return self::fail('Invalid login.');
		}

		$admin->lastlogin = date('Y-m-d H:i:s');

		if (!$admin->save()) {
			return self::fail();
		}

		$session = $admin->createSession();

		Lib\Cookie::set(hash('sha256', Config::$adminkey), $session->identifier);

		return self::success();
	}

	public static function logout()
	{
		$cookie = Lib::cookie();
		$key = hash('sha256', Config::$adminkey);

		$identifier = $cookie->get($key);

		Table\AdminSession::destroy(array('identifier' => $identifier));

		Lib\Cookie::delete($key);

		return true;
	}
}
