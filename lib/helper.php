<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

class Helper
{
	public static function getInstance()
	{
		return new static();
	}
}
