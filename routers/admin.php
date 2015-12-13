<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminRouter extends Router
{
	public $allowedRoute = 'admin';
	public $allowedBuild = 'admin';

	// Segments
	// view/type/subtype
	// controller/type

	public function encode($key, &$options, &$segments)
	{
		// There could be view=admin or controller=admin

		if (isset($options['view'])) {
			$segments[] = urlencode($options['view']);
			unset($options['view']);
		}

		if (isset($options['controller'])) {
			$segments[] = urlencode($options['controller']);
			unset($options['controller']);
		}

		if (isset($options['type'])) {
			$segments[] = urlencode($options['type']);
			unset($options['type']);
		}

		if (isset($options['subtype'])) {
			$segments[] = urlencode($options['subtype']);
			unset($options['subtype']);
		}
	}

	public function decode($segments)
	{
		if (count($segments) === 1) {
			Req::set('GET', 'view', 'admin');
			return;
		}

		$systemKey = array('login', 'logout', 'create');

		if (in_array($segments[1], $systemKey)) {
			Req::set('GET', 'controller', 'admin');
		} else {
			Req::set('GET', 'view', 'admin');
		}

		Req::set('GET', 'type', $segments[1]);

		if (!empty($segments[2])) {
			Req::set('GET', 'subtype', $segments[2]);
		}
	}
}
