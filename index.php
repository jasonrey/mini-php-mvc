<?php
!defined('SERVER_EXEC') && define('SERVER_EXEC', true);

require_once(dirname(__FILE__) . '/lib/lib.php');

Lib::load('table');

class ATable extends Table
{
	public $tablename = 'a';
	public $test;
	public $test2;
}

$a = new ATable();

$a->load(3);

var_dump($a);

// Lib::route();
