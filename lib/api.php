<?php namespace Mini\Lib;
!defined('SERVER_EXEC') && die('No access.');

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

	public function __callStatic($method, $arguments)
	{
		return self::fail('No such method.');
	}
}
