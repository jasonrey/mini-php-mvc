<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminsessionTable extends Table
{
	public $tablename = 'adminsession';
	public $admin_id;
	public $identifier;
	public $date;
	public $data;
}
