<?php
!defined('SERVER_EXEC') && die('No access.');

if (!Req::haspost('username') || !Req::haspost('password') || !Req::haspost('ref')) {
	Lib::error();
}

$password = Config::getAdminConfig(Req::post('username'));

$ref = urldecode(Req::post('ref'));

if ($password !== Lib::hash(Req::post('password'))) {
	Lib::redirect(Lib::url('admin', array('ref' => $ref)));
	return;
}

Lib::cookie()->set(Lib::hash(Config::$adminkey), 1);

$segments = explode('/', base64_decode($ref));

$base = array_shift($segments);
$type = array_shift($segments);
$subtype = array_shift($segments);

$options = array();

if (!empty($type)) {
	$options['type'] = $type;
}

if (!empty($subtype)) {
	$options['subtype'] = $subtype;
}

Lib::redirect($base, $options);
return;
