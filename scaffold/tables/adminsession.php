<?php namespace Mini\Table;
!defined('MINI_EXEC') && die('No access.');

class AdminSession extends \Mini\Lib\Table
{
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
