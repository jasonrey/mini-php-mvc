<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminView extends View
{
	public function display()
	{
		$key = Lib::hash(Config::$adminkey);

		$cookie = Lib::cookie();

		$logged = $cookie->get($key);

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
			$options = array();

			if (!empty($type)) {
				$options['type'] = $type;
			}

			$subtype = Req::get('subtype');
			if (!empty($subtype)) {
				$options['subtype'] = $subtype;
			}

			$ref = Lib::url('admin', $options);

			Lib::redirect('admin', array('ref' => base64_encode($ref)));
			return;
		}

		if (empty($type) || $type == 'index') {
			return $this->index();
		}

		if (!is_callable(array($this, $type))) {
			echo Lib::view('error')->display();
			return;
		}

		return $this->$type();
	}

	public function system()
	{
		$subtype = Req::get('subtype');

		if (empty($subtype)) {
			return Lib::redirect('admin');
		}

		$api = Lib::api('admin', array('response' => 'return', 'format' => 'php'));

		if (!is_callable(array($api, $subtype))) {
			echo Lib::view('error')->display();
			return;
		}

		$result = $api->$subtype();

		switch ($subtype) {
			case 'verify':
				$ref = Req::post('ref');

				if (!$result['state']) {
					Lib::redirect('admin', array('ref' => $ref));
				} else {
					Lib::cookie()->set(Lib::hash(Config::$adminkey), 1);

					$segments = explode('/', base64_decode(urldecode($ref)));

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
				}
			break;

			case 'logout':
				Lib::redirect('admin');
			break;
		}

		return;
	}

	public function index()
	{
		echo $this->includeTemplate('index');
	}

	public function form()
	{
		$ref = Req::get('ref');

		$this->set('ref', $ref);

		echo $this->includeTemplate('form');
	}
}
