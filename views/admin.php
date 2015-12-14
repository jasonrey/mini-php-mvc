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

		$logged = !empty($identifier) && $admin->load(array('identifier' => $identifier));

		$type = Req::get('type');

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

		if (empty($type)) {
			$type = 'index';
		}

		if (!is_callable(array($this, $type))) {
			return Lib::redirect('error');
		}

		return $this->$type();
	}

	public function form()
	{
		$ref = Req::get('ref');

		$this->set('ref', $ref);

		$model = Lib::model('admin');

		if (!$model->hasAdmins()) {
			$this->template = 'formcreate';

			return;
		}

		$this->template = 'form';
	}

	public function index()
	{

	}
}
