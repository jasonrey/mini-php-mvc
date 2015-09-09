<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminApi extends Api
{
	public function verify()
	{
		if (!Req::haspost('username') || !Req::haspost('password') || !Req::haspost('ref')) {
			return $this->fail();
		}

		$password = Config::getAdminConfig(Req::post('username'));

		if ($password !== Lib::hash(Req::post('password'))) {
			return $this->fail();
		}

		Lib::cookie()->set(Lib::hash(Config::$adminkey), 1);
		return $this->success();
	}
}
