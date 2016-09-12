<?php namespace Mini\Lib\ViewRenderer;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib;

class Pug extends \Mini\Lib\ViewRenderer
{
	private static $engine;

	public static $extension = 'pug';

	public function __construct($view = null)
	{
		parent::__construct($view);

		if (empty(self::$engine)) {
			self::$engine = new \Pug\Pug(array(
				'prettyprint' => false,
				'extension' => '.pug',
				'basedir' => Lib\Path::resolve('templates')
			));
		}
	}

	public function output($template)
	{
		$content = parent::output($template);

		return self::$engine->render($content, array_merge(get_object_vars($this->view), $this->vars));
	}
}
