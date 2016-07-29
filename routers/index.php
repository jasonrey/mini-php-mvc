<?php namespace Mini;
!defined('SERVER_EXEC') && die('No access.');

use \Mini\Lib\Router;

Router::get('/', function() {
	Lib\Req::get('view', 'index');
});
