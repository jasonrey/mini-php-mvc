<?php
!defined('SERVER_EXEC') && die('No access.');

class AdminRouter extends Router
{
	public $segments = array('type');

	public function route($segments = array())
	{
		$result = $this->decode($segments);

		$view = Lib::view($this->key);

		$view->display();

		return true;
	}

	public function decode($segments = array())
	{
		if (isset($segments[1])) {
			Req::set('GET', 'subtype', $segments[1]);
		}

		return parent::decode($segments);
	}

	public function encode(&$options = array())
	{
		$segments = parent::encode($options);

		if (isset($options['subtype'])) {
			$segments[] = $options['subtype'];
			unset($options['subtype']);
		}

		return $segments;
	}
}
