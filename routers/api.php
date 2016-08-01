<?php namespace Mini\Router;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib\Router;

Router::post('/api/:type/|:action');
