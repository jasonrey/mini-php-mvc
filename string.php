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

	public static function generateHash($length = 64)
	{
		$random = String::hash(rand() . time());
		$maxLength = strlen($random);
		$length = min($maxLength, max(0, $length));
		$start = rand(0, $maxLength - $length);

		return substr($random, $start, $length);
	}
}
