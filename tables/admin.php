<?php namespace Mini\Table;
!defined('MINI_EXEC') && die('No access.');

class Admin extends \Mini\Lib\Table
{
	public static $tablename = 'admin';
	public static $columns = array(
		'id' => 'int',
		'username' => 'varchar',
		'password' => 'varchar',
		'salt' => 'varchar',
		'lastlogin' => 'datetime',
		'date' => 'datetime'
	);

	public function checkPassword($password)
	{
		return hash('sha256', $this->username . $password . $this->salt) === $this->password;
	}

	public function setPassword($password)
	{
		$this->salt = self::generateHash();
		$this->password = hash('sha256', $this->username . $password . $this->salt);
	}

	public function createSession()
	{
		$session = AdminSession::create(array(
			'admin_id' => $this->id,
			'identifier' => self::generateHash(),
			'data' => json_encode($_SERVER)
		));

		return $session;
	}

	public function set($key, $value)
	{
		if ($key === 'password') {
			return $this->setPassword($value);
		}

		return parent::set($key, $value);
	}

	private static function generateHash($length = 64)
	{
		$random = hash('sha256', rand());
		$maxLength = strlen($random);
		$length = min($maxLength, max(0, $length));
		$start = rand(0, $maxLength - $length);

		return substr($random, $start, $length);
	}

	public static function hasAdmins()
	{
		$count = self::count();

		return $count > 0;
	}
}
