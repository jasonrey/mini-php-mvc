<?php namespace Mini\View;
!defined('SERVER_EXEC') && die('No access.');

class Error extends \Mini\Lib\View
{
	public static function display()
	{
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

		return parent::display();
	}
}
