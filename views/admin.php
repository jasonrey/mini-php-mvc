<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminView extends View
{
	public $css = 'admin';

	public function main()
	{
		$key = Lib::hash(Config::$adminkey);

		$cookie = Lib::cookie();

		$identifier = $cookie->get($key);

		$admin = Lib::table('admin');

		$logged = $admin->load(array('identifier' => $identifier));

		$type = Req::get('type');

		// Exception to type === 'system'
		if ($type === 'system') {
			return $this->system();
		}

		$ref = Req::get('ref');

		if (!empty($ref)) {
			if ($logged) {
				$segments = explode('/', base64_decode($ref));

				$base = array_shift($segments);
				$type = array_shift($segments);
				$subtype = array_shift($segments);

				$options = array();

				if (!empty($type)) {
					$options['type'] = $type;
				}

				if (!empty($subtype)) {
					$options['subtype'] = $subtype;
				}

				Lib::redirect($base, $options);
				return;
			}

			return $this->form();
		}

		if (!$logged) {
			if (empty($type)) {
				return $this->form();
			}

			$options = array('view' => 'admin');

			if (!empty($type)) {
				$options['type'] = $type;
			}

			$subtype = Req::get('subtype');

			if (!empty($subtype)) {
				$options['subtype'] = $subtype;
			}

			$ref = Lib::url('admin', $options);

			return Lib::redirect('admin', array('view' => 'admin', 'ref' => base64_encode($ref)));
		}

		if (!is_callable(array($this, $type))) {
			return Lib::redirect('error');
		}

		return $this->$type();
	}

	public function system()
	{
		$subtype = Req::get('subtype');

		if (empty($subtype)) {
			return Lib::redirect('admin', array('view' => 'admin'));
		}

		$api = Lib::api('admin', array('response' => 'return', 'format' => 'php'));

		if (!is_callable(array($api, $subtype))) {
			return Lib::redirect('error');
		}

		$result = $api->$subtype();

		$options = array('view' => 'admin');

		switch ($subtype) {
			case 'login':
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
			break;

			case 'logout':
			break;
		}

		Lib::redirect('admin', $options);
	}

	public function form()
	{
		$ref = Req::get('ref');

		$this->set('ref', $ref);

		$this->template = 'form';
	}
}
