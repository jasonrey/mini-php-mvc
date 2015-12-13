<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminModel extends Model
{
	public $tablename = 'admin';

	public function hasAdmins()
	{
		$query = 'SELECT COUNT(1) FROM ' . $this->db->quoteName($this->tablename);

		return $this->getCell($query) > 0;
	}

	public function getAdmins()
	{
		$query = 'SELECT * FROM ' . $this->db->quoteName($this->tablename);

		return $this->getResult($query);
	}
}
