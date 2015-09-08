<?php
!defined('SERVER_EXEC') && die('No access.');

class Router
{
	/* Used to link with view */
	public $key;

	/* Used as base for url */
	public $base;

	public $segments = array();

	private static $instances = array();

	public static function getInstance($name)
	{
		$key = trim(strtolower(preg_replace('/[' . preg_quote('-_', '/') . ']/', '', $name)));

		$state = Lib::load('router', $key);

		if (!$state) {
			return false;
		}

		if (!isset(self::$instances[$key])) {
			$classname = ucfirst($key) . 'Router';

			self::$instances[$key] = new $classname;

			self::$instances[$key]->key = $key;
			self::$instances[$key]->base = $name;
		}

		return self::$instances[$key];
	}

	public function route($segments = array())
	{
		$result = $this->decode($segments);

		$view = Lib::view($this->key);

		$view->display();
	}

	public function decode($segments = array())
	{
		$total = count($segments);

		foreach ($segments as $index => $value) {
			if (!isset($this->segments[$index])) {
				continue;
			}

			Req::set('GET', $this->segments[$index], $value);
		}
	}

	public function build($options = array())
	{
		$link = $this->base;

		$segments = $this->encode($options);

		if (!empty($segments)) {
			$link .= '/' . implode('/', $segments);
		}

		if (!empty($options)) {
			$link .= $this->buildQueries($options);
		}

		return $link;
	}

	public function encode(&$options = array())
	{
		$segments = array();

		foreach ($this->segments as $index => $key) {
			if (!isset($options[$key])) {
				continue;
			}

			$segments[] = $options[$key];
			unset($options[$key]);
		}

		return $segments;
	}

	public function buildQueries($options = array())
	{
		if (empty($options)) {
			return '';
		}

		foreach ($options as $k => $v) {
			$values[] = $k . '=' . $v;
		}

		$queries = implode('&', $values);

		if (!empty($queries)) {
			$queries = '?' . $queries;
		}

		return $queries;
	}
}
