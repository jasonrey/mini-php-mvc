<?php namespace Mini\Table;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib;
use \Mini\Config;

class Admin extends \Mini\Lib\Table
{
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
		return Lib\String::hash($this->username . $password . $this->salt) === $this->password;
	}

	public function setPassword($password)
	{
		$this->salt = self::generateHash();
		$this->password = Lib\String::hash($this->username . $password . $this->salt);
	}

	public function createSession()
	{
		$session = AdminSession::create(array(
			'admin_id' => $this->id,
			'identifier' => self::generateHash(),
			'data' => json_encode($_SERVER)
		));

		Lib\Cookie::setIdentifier('admin', $session->identifier);

		return $session;
	}

	private static function generateHash($length = 64)
	{
		$random = Lib\String::hash(rand());
		$maxLength = strlen($random);
		$length = min($maxLength, max(0, $length));
		$start = rand(0, $maxLength - $length);

		return substr($random, $start, $length);
	}

	public static function isLoggedIn()
	{
		$identifier = Lib\Cookie::getIdentifier('admin');

		$session = AdminSession::get(array('identifier' => $identifier));

		$logged = !empty($identifier) && !$session->error;

		return $logged;
	}

	public static function getAdmin()
	{
		$identifier = Lib\Cookie::getIdentifier('admin');

		$session = AdminSession::get(array('identifier' => $identifier));

		return Admin::get($session->admin_id);
	}

	public static function hasAdmins()
	{
		$count = self::count();

		return $count > 0;
	}
}
