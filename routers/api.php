<?php
!defined('SERVER_EXEC') && die('No access.');

class ApiRouter extends Router
{
	public $segments = array('type', 'action');

	public function route($segments = array())
	{
		$path = dirname(__FILE__) . '/../api/' . implode('/', $segments) . '.php';

		if (!file_exists($path)) {
			return false;
		}

		require($path);

		return true;
	}
}
