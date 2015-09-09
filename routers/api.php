<?php
!defined('SERVER_EXEC') && die('No access.');

class ApiRouter extends Router
{
	public $segments = array('type', 'action');

	public function route($segments = array())
	{
		$name = array_shift($segments);
		$method = array_shift($segments);

		$api = Lib::api($name);

		if (!is_callable(array($api, $method))) {
			return Lib::api()->fail();
		}

		$api->$method($segments);
	}
}
