<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminTable extends Table
{
	public $username;
	public $password;
	public $salt;
	public $identifier;
	public $lastlogin;
	public $date;

	public function store()
	{
		if (empty($this->date)) {
			$this->date = date('Y-m-d H:i:s');
		}

		return parent::store();
	}

	public function login()
	{
		if (empty($this->username) && empty($this->password)) {
			if (func_num_args() < 2) {
				return false;
			}

			list($username, $password) = func_get_args();

			if (!$this->load(array('username' => $username))) {
				return false;
			}

			if (!$this->checkPassword($password)) {
				return false;
			}
		}

		$this->lastlogin = date('Y-m-d H:i:s');

		$this->identifier = $this->generateHash();

		if (!$this->store()) {
			return false;
		}

		Lib::cookie()->set(hash('sha256', Config::$adminkey), $this->identifier);

		return true;
	}

	public function logout()
	{
		$cookie = Lib::cookie();
		$key = hash('sha256', Config::$adminkey);

		$identifier = $cookie->get($key);

		if ($this->load(array('identifier' => $identifier))) {
			$this->identifier = '';
			$this->store();
		}

		Lib::cookie()->delete($key);

		return true;
	}

	public function checkPassword($password)
	{
		return hash('sha256', $password . $this->salt) === $this->password;
	}

	public function setPassword($password)
	{
		$this->salt = $this->generateHash();
		$this->password = hash('sha256', $password . $this->salt);
	}

	private function generateHash($length = 64)
	{
		$random = hash('sha256', rand());
		$maxLength = strlen($random);
		$length = min($maxLength, max(0, $length));
		$start = rand(0, $maxLength - $length);

		return substr($random, $start, $length);
	}
}
