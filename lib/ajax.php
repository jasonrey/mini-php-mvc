<?php

!defined('SERVER_EXEC') && die('No access.');

class Ajax
{
	public static $instance;

	public static function init()
	{
		if (empty(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function fail($data = '')
	{
		$response = array(
			'status' => 'fail',
			'data' => $data
		);

		return $this->send($response);
	}

	public function success($data = '')
	{
		$response = array(
			'status' => 'success',
			'data' => $data
		);

		return $this->send($response);
	}

	public function send($response = '')
	{
		if (is_object($response) || is_array($response)) {
			$response = json_encode($response);
		}

		echo $response;
		exit;
	}
}