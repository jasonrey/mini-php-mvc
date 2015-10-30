<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminRouter extends Router
{
	public $allowedRoute = 'admin';
	public $allowedBuild = 'admin';
	public $segments = array('view', 'type', 'subtype');
}
