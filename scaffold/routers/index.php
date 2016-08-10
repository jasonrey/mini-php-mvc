<?php namespace Mini\Router;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib\Router;
use \Mini\Lib\Req;

Router::get('/', function($params) {
	if (!Req::hasget('view')) {
		Req::get('view', 'index');
	}
});

Router::build('/');
Router::build('/:view=index', function(&$params) {
	unset($params['view']);
	return '';
});
