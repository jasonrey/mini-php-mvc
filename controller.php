<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

class Controller
{
	public static function execute()
	{
		// Controllers are static links that is not used for API purposes, hence it should always redirect to something after performing internal actions
		Lib::redirect(array('view' => 'index'));
	}
}
