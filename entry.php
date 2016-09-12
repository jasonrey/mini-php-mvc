<?php namespace Mini;
!defined('MINI_EXEC') && define('MINI_EXEC', true);

$base = dirname(dirname($_SERVER['SCRIPT_FILENAME']));

// Composer autoload
$composerAutoload = $base . '/vendor/autoload.php';

if (file_exists($composerAutoload)) {
	require $composerAutoload;
}

// Internal library autoload
spl_autoload_register(function($class) use ($base) {
	$segs = explode('\\', $class);

	if ($segs[0] !== 'Mini') {
		return;
	}

	$total = count($segs);

	$file = '';

	if ($segs[1] === 'Lib') {
		if ($total === 3) {
			$file = $base . '/lib/' . strtolower($segs[2]) . '.php';
		}

		if ($total === 4) {
			switch ($segs[2]) {
				case 'DatabaseAdapter':
					$file = $base . '/lib/database-adapters/' . strtolower($segs[3]) . '.php';
				break;
				case 'ViewRenderer':
					$file = $base . '/lib/view-renderers/' . strtolower($segs[3]) . '.php';
				break;
			}
		}
	} else if ($segs[1] === 'Config') {
		$file = $base . '/config.php';
	} else {
		if ($total === 3) {
			$file = $base . '/' . strtolower($segs[1] . 's/' . $segs[2]) . '.php';
		}
	}

	if (file_exists($file)) {
		require $file;
	}
});

// Fallback library
// v2.0 - Deprecated
require $base . '/lib/lib.php';

// Set error reporting
if (Config::env() !== 'production') {
	ini_set('error_rerpoting', E_ALL);
	ini_set('display_errors', true);
}

// Set basepath
if (empty(Config::$basepath)) {
	Config::$basepath = $base;
}

// Initiate session
Lib\Session::init();

// Load constant
require Lib\Path::resolve('constant.php');

// Include node modules binary
if (Config::env() === 'development') {
	putenv('PATH=' . getenv('PATH') . ':' . Lib\Path::resolve('node_modules/.bin'));
}

// Initiate route
Lib\Router::route();
