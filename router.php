<?php
!defined('SERVER_EXEC') && die('No access.');

class Router
{
	public $base;
	public $segments = array();

	public function route($segments = array())
	{
		$result = $this->decode($segments);

		if ($result === false) {
			return false;
		}

		$view = Lib::view($this->base);

		echo $view->display();

		return true;
	}

	public function decode($segments = array())
	{
		$total = count($segments);

		if ($total < count($this->segments)) {
			return false;
		}

		foreach ($this->segments as $index => $key) {
			Req::set('GET', $key, $segments[$index]);
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