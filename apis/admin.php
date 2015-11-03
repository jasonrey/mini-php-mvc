<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminApi extends Api
{
	public function login()
	{
		$keys = array('username', 'password');

		if (!Req::haspost($keys)) {
			return $this->fail();
		}

		$post = Req::post($keys);
		extract($post);

		$admin = Lib::table('admin');

		if (!$admin->login($username, $password)) {
			return $this->fail();
		}

		return $this->success();
	}

	public function logout()
	{
		return Lib::table('admin')->logout();
	}

	public function create()
	{
		$keys = array('username', 'password');

		if (!Req::haspost($keys)) {
			return $this->fail();
		}

		$referral = Req::post('referral');

		if (empty($referral) && Lib::model('admin')->hasAdmins()) {
			return $this->fail();
		}

		$post = Req::post($keys);
		extract($post);

		$admin = Lib::table('admin');
		$admin->username = $username;
		$admin->setPassword($password);

		if (!$admin->store()) {
			return $this->fail();
		}

		$admin->login();

		return $this->success();
	}
}
