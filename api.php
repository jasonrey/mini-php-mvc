<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

class Api
{
	public static function fail($data = '')
	{
		$response = array(
			'state' => false,
			'status' => 'fail',
			'data' => $data
		);

		return self::send($response);
	}

	public static function success($data = '')
	{
		$response = array(
			'state' => true,
			'status' => 'success',
			'data' => $data
		);

		return self::send($response);
	}

	public static function send($response = '')
	{
		return $response;
	}

	public static function __callStatic($method, $arguments)
	{
		return self::fail('No such method.');
	}
}
