<?php namespace Mini;
!defined('MINI_EXEC') && define('MINI_EXEC', true);

$base = dirname(dirname($_SERVER['SCRIPT_FILENAME']));

require $base . '/lib/lib.php';

Lib::init($base);
