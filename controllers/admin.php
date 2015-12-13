<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminController extends Controller
{
	public function execute()
	{
		$api = Lib::api('admin', array('response' => 'return', 'format' => 'php'));

		$type = Req::get('type');

		if (!is_callable(array($api, $type))) {
			return Lib::redirect('error');
		}

		$result = $api->$type();


		$options = array('view' => 'admin');

		$ref = Req::post('ref');

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

		Lib::redirect('admin', $options);
	}
}
