<?php namespace \Mini\Controller;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib;
use \Mini\Config;

class Admin extends \Mini\Lib\Controller
{
	public static function execute()
	{
		$api = Lib::api('admin', array('response' => 'return', 'format' => 'php'));

		$type = Lib\Req::get('type');

		if (!is_callable(array($api, $type))) {
			return Lib::redirect(array('view' => 'error'));
		}

		$result = $api->$type();

		$options = array('view' => 'admin');

		$ref = Lib\Req::post('ref');

		if (!$result['state']) {
			if (!empty($ref)) {
				$options['ref'] = $ref;
			}
		} else {
			$segments = explode('/', base64_decode(urldecode($ref)));

			$base = array_shift($segments);
			$type = array_shift($segments);
			$subtype = array_shift($segments);

			if (!empty($type)) {
				$options['type'] = $type;
			}

			if (!empty($subtype)) {
				$options['subtype'] = $subtype;
			}
		}

		Lib::redirect($options);
	}
}
