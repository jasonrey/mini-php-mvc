<?php namespace Mini\Router;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib\Router;
use \Mini\Lib\Req;

Router::get('/', function($params) {
	Req::get('view', 'index');
});
