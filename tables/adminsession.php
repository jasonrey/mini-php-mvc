<?php namespace Mini\Table;
!defined('SERVER_EXEC') && die('No access.');

class Adminsession extends \Mini\Lib\Table
{
	public $tablename = 'adminsession';

	public static $columns = array(
		'id' => 'int',
		'admin_id' => 'int',
		'identifier' => 'varchar',
		'date' => 'datetime',
		'data' => 'text'
	);

	public static $foreigns = array(
		'admin_id' => array(
			'classname' => 'AdminTable',
			'column' => 'id'
		)
	);
}
