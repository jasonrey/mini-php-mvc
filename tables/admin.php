<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminTable extends Table
{
	public $tablename = 'admin';
	public $username;
	public $password;
	public $salt;
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

		if (!$this->store()) {
			return false;
		}

		$adminsession = Lib::table('adminsession');

		$adminsession->admin_id = $this->id;
		$adminsession->identifier = $this->generateHash();
		$adminsession->date = date('Y-m-d H:i:s');
		$adminsession->data = json_encode($_SERVER);

		$adminsession->save();

		Lib::cookie()->set(hash('sha256', Config::$adminkey), $adminsession->identifier);

		return true;
	}

	public function logout()
	{
		$cookie = Lib::cookie();
		$key = hash('sha256', Config::$adminkey);

		$identifier = $cookie->get($key);

		$adminsession = Lib::table('adminsession');
		if ($adminsession->load(array('identifier' => $identifier))) {
			$adminsession->delete();
		}

		Lib::cookie()->delete($key);

		return true;
	}

	public function checkPassword($password)
	{
		return hash('sha256', $this->username . $password . $this->salt) === $this->password;
	}

	public function setPassword($password)
	{
		$this->salt = $this->generateHash();
		$this->password = hash('sha256', $this->username . $password . $this->salt);
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
