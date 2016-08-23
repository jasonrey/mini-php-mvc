<?php
!defined('MINI_EXEC') && die('No access.');

class V2ViewRenderer extends ViewRenderer
{
	private $base;

	public function using($name, $vars = array())
	{
		$this->base = $name;
	}

	public function start()
	{

	}

	public function stop()
	{

	}

	public function block()
	{

	}
}

class V2ViewRendererItem
{

}
