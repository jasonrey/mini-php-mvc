<?php
!defined('SERVER_EXEC') && die('No access.');

class IndexRouter extends Router
{
	public $allowedBuild = 'index';

	public function decode($segments)
	{
		if (empty($segments[0])) {
			Req::set('GET', 'view', 'index');
		}
	}
}
