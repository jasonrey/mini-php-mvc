<?php

!defined('SERVER_EXEC') && die('No access.');

class Api
{
	private $response = 'echo';
	private $format = 'json';

	private static $instances = array();

	public static function getInstance($name, $options = array())
	{
		$state = Lib::load('api', $name);

		if (!$state) {
			return false;
		}

		if (!isset(self::$instances[$name])) {
			$classname = ucfirst($name) . 'Api';

			self::$instances[$name] = new $classname;
		}

		self::$instances[$name]->config($options);

		return self::$instances[$name];
	}

	public function config($key, $value = null)
	{
		if (is_array($key)) {
			if (isset($key['response'])) {
				$this->response = $key['response'];
			}

			if (isset($key['format'])) {
				$this->format = $key['format'];
			}
		} else {
			if (!empty($key)) {
				$this->$key = $value;
			}
		}
	}

	public function fail($data = '')
	{
		$response = array(
			'state' => false,
			'status' => 'fail',
			'data' => $data
		);

		return $this->send($response);
	}

	public function success($data = '')
	{
		$response = array(
			'state' => true,
			'status' => 'success',
			'data' => $data
		);

		return $this->send($response);
	}

	public function send($response = '')
	{
		if ($this->format === 'json') {
			if (is_object($response) || is_array($response)) {
				$response = json_encode($response);
			}
		}

		if ($this->response === 'echo') {
			echo $response;
			exit;
		}

		return $response;
	}

	public function __call($method, $arguments)
	{
		return $this->fail();
	}
}
