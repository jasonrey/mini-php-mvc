<?php
!defined('SERVER_EXEC') && die('No access.');

class ApiRouter extends Router
{
	public $allowedRoute = 'api';
	public $allowedBuild = 'api';

	public $segments = array('api', 'action');

	public function decode($segments)
	{
		if (count($segments) >= 3) {
			$view = array_shift($segments);
			$api = array_shift($segments);
			$action = array_shift($segments);

			Req::set('GET', 'api', $api);
			Req::set('GET', 'action', $action);
		}
	}

	public function encode($key, &$options, &$segments)
	{
		$segments[] = 'api';

		return parent::encode($key, $options, $segments);
	}
}
