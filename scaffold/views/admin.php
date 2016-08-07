<?php namespace Mini\View;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib;
use \Mini\Config;
use \Mini\Table;

class Admin extends \Mini\Lib\View
{
	public $css = 'admin';

	public function main()
	{
		$logged = Table\Admin::isLoggedIn();

		$type = Lib\Req::get('type');

		$ref = Lib\Req::get('ref');

		if (!empty($ref)) {
			if ($logged) {
				$segments = explode('/', base64_decode($ref));

				$base = array_shift($segments);
				$type = array_shift($segments);
				$subtype = array_shift($segments);

				$options = array('view' => 'admin');

				if (!empty($type)) {
					$options['type'] = $type;
				}

				if (!empty($subtype)) {
					$options['subtype'] = $subtype;
				}

				Lib::redirect($options);
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

			$subtype = Lib\Req::get('subtype');

			if (!empty($subtype)) {
				$options['subtype'] = $subtype;
			}

			$ref = Lib::url($options);

			return Lib::redirect(array('view' => 'admin', 'ref' => base64_encode($ref)));
		}

		if (empty($type)) {
			$type = 'index';
		}

		if (!is_callable(array($this, $type))) {
			return Lib::redirect(array('view' => 'admin'));
		}

		return $this->$type();
	}

	public function form()
	{
		$ref = Lib\Req::get('ref');

		$this->set('ref', $ref);

		$this->set('errorMessage', Lib\Session::getError());

		if (!Table\Admin::hasAdmins()) {
			$actionUrl = Lib::url(array(
				'controller' => 'admin',
				'action' => 'create'
			));

			$this->set('actionUrl', $actionUrl);

			$this->template = 'formcreate';

			return;
		}

		$actionUrl = Lib::url(array(
			'controller' => 'admin',
			'action' => 'login'
		));

		$this->set('actionUrl', $actionUrl);

		$this->template = 'form';
	}

	public function index()
	{
		$actionUrl = Lib::url(array(
			'controller' => 'admin',
			'action' => 'logout'
		));

		$this->set('actionUrl', $actionUrl);
	}
}