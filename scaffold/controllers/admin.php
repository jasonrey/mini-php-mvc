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

		$redirectOptions = ['view' => 'admin'];

		if (!empty($ref)) {
			$redirectOptions['ref'] = $ref;
		}

		if (!Lib\Req::haspost($keys)) {
			return Lib::redirect($redirectOptions);
		}

		// Only allow one time creation
		if (Table\Admin::hasAdmins()) {
			return Lib::redirect($redirectOptions);
		}

		$post = Lib\Req::post($keys);
		extract($post);

		$admin = Table\Admin::create(array(
			'username' => $username,
			'password' => $password
		));

		if ($admin->error) {
			return Lib::redirect($redirectOptions);
		}

		$session = $admin->createSession();

		Lib\Cookie::set(hash('sha256', Config::$adminkey), $session->identifier);

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
}
