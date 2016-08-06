<?php namespace Mini;
!defined('MINI_EXEC') && define('MINI_EXEC', true);

require __DIR__ . '/vendor/autoload.php';

require_once dirname(__FILE__) . '/lib/lib.php';

Lib::init();

Lib\Router::route();
