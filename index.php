<?php namespace Mini;
!defined('SERVER_EXEC') && define('SERVER_EXEC', true);

require __DIR__ . '/vendor/autoload.php';

require_once dirname(__FILE__) . '/lib/lib.php';

Lib::init();

Lib\Router::route();
