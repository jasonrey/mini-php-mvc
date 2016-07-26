<?php
!defined('SERVER_EXEC') && define('SERVER_EXEC', true);

require_once dirname(__FILE__) . '/lib/lib.php';

require __DIR__ . '/vendor/autoload.php';

Lib::route();
