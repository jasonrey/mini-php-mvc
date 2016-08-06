<?php namespace Mini\Router;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib\Router;
use \Mini\Lib\Req;

Router::post('/admin/:action/|:subaction', function($params) {
	Req::get('controller', 'admin');

	Req::get('action', $params['action']);

	if (isset($params['subaction'])) {
		Req::get('subaction', $params['subaction']);
	}
});

Router::get('/admin/|:type/|:subtype', function($params) {
	Req::get('view', 'admin');

	if (isset($params['type'])) {
		Req::get('type', $params['type']);
	}

	if (isset($params['subtype'])) {
		Req::get('subtype', $params['subtype']);
	}
});

Router::build('/:controller=admin/:action/|:subaction');
Router::build('/:view=admin/|:type=foo/|:subtype');
