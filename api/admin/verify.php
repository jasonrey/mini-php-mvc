<?php
!defined('SERVER_EXEC') && die('No access.');

if (!Req::haspost('username') || !Req::haspost('password') || !Req::haspost('ref')) {
	Lib::error();
}

$password = Config::getAdminConfig(Req::post('username'));

$ref = Req::post('ref');

if ($password !== Lib::hash(Req::post('password'))) {
	Lib::redirect(Lib::url('admin', array('ref' => $ref)));
	return;
}

Lib::cookie()->set(Lib::hash(Config::$adminkey), 1);

Lib::redirect(base64_decode($ref));
return;
