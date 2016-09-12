<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

class String
{
	public static function hash($string)
	{
		return hash('sha256', $string);
	}

	public static function escape($string)
	{
		return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}
}
