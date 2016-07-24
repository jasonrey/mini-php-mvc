<?php
!defined('SERVER_EXEC') && define('SERVER_EXEC', true);

require_once(dirname(__FILE__) . '/lib/lib.php');

Lib::load('table');

class ATable extends Table
{
	public static $tablename = 'a';
	public $a;
	public $a2;
	public $a3;
}

class BTable extends Table
{
	public static $activedb = 'new';
	public static $tablename = 'a';
	public $a;
	public $a2;
	public $a3;
}

class CTable extends Table
{
	public static $tablename = 'c';
	public $a;
	public $a2;
	public $a3;
}

$a = ATable::all();
$b = BTable::all();

var_dump($a, $b);

// Lib::route();
