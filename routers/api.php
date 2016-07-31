<?php namespace Mini\Router;
!defined('SERVER_EXEC') && die('No access.');

use \Mini\Lib\Router;

Router::post('/api/:type/|:action');
