<?php namespace Mini\View;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib;
use \Mini\Config;
use \Mini\Table;

class Index extends \Mini\Lib\View
{
	public $css = array();

	public function main()
	{
		$logged = Table\Admin::isLoggedIn();

		if (!$logged) {
			return $this->form();
		}
	}

	public function form()
	{
		$this->css[] = 'admin';

		if (!Table\Admin::hasAdmins()) {
			$actionUrl = Lib::url(array(
				'controller' => 'admin',
				'type' => 'create'
			));

			$this->set('actionUrl', $actionUrl);

			$this->template = 'formcreate';

			return;
		}

		$actionUrl = Lib::url(array(
			'controller' => 'admin',
			'type' => 'login'
		));

		$this->set('actionUrl', $actionUrl);

		$this->template = 'form';
	}
}
