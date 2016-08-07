<?php namespace Mini\Controller;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib;
use \Mini\Config;
use \Mini\Table;

class Admin extends \Mini\Lib\Controller
{
	public static function create()
	{
		$keys = array('username', 'password');

		$ref = Lib\Req::post('ref');

		$redirectOptions = array('view' => 'admin');

		if (!empty($ref)) {
			$redirectOptions['ref'] = $ref;
		}

		if (!Lib\Req::haspost($keys)) {
			Lib\Session::setError('Insufficient data.');

			return Lib::redirect($redirectOptions);
		}

		// Only allow one time creation
		if (Table\Admin::hasAdmins()) {
			Lib\Session::setError('Admin already exist.');

			return Lib::redirect($redirectOptions);
		}

		$post = Lib\Req::post($keys);
		extract($post);

		$admin = new Table\Admin(array(
			'username' => $username,
			'lastlogin' => date('Y-m-d H:i:s')
		));

		$admin->setPassword($password);

		if (!$admin->save()) {
			Lib\Session::setError($admin->error);
			return Lib::redirect($redirectOptions);
		}

		$session = $admin->createSession();

		$segments = explode('/', base64_decode(urldecode($ref)));

		$base = array_shift($segments);
		$type = array_shift($segments);
		$subtype = array_shift($segments);

		unset($redirectionOptions['ref']);

		if (!empty($type)) {
			$redirectOptions['type'] = $type;
		}

		if (!empty($subtype)) {
			$redirectOptions['subtype'] = $subtype;
		}

		return Lib::redirect($redirectOptions);
	}

	public static function login()
	{
		$keys = array('username', 'password');

		$ref = Lib\Req::post('ref');

		$redirectOptions = array('view' => 'admin');

		if (!empty($ref)) {
			$redirectOptions['ref'] = $ref;
		}

		if (!Lib\Req::haspost($keys)) {
			Lib\Session::setError('Insufficient data.');

			return Lib::redirect($redirectOptions);
		}

		$post = Lib\Req::post($keys);
		extract($post, EXTR_SKIP);

		$admin = Table\Admin::get(array('username' => $username));

		if ($admin->error || !$admin->checkPassword($password)) {
			Lib\Session::setError('Invalid login.');
			return Lib::redirect($redirectOptions);
		}

		$admin->lastlogin = date('Y-m-d H:i:s');

		if (!$admin->save()) {
			Lib\Session::setError($admin->error);
			return Lib::redirect($redirectOptions);
		}

		$session = $admin->createSession();

		$segments = explode('/', base64_decode(urldecode($ref)));

		$base = array_shift($segments);
		$type = array_shift($segments);
		$subtype = array_shift($segments);

		unset($redirectionOptions['ref']);

		if (!empty($type)) {
			$redirectOptions['type'] = $type;
		}

		if (!empty($subtype)) {
			$redirectOptions['subtype'] = $subtype;
		}

		return Lib::redirect($redirectOptions);
	}

	public static function logout()
	{
		$identifier = Lib\Cookie::getIdentifier('admin');

		Table\AdminSession::destroy(array('identifier' => $identifier));

		Lib\Cookie::deleteIdentifier('admin');

		return Lib::redirect(array(
			'view' => 'admin'
		));
	}
}
