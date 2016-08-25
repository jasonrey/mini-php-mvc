<?php namespace Mini\Lib\ViewRenderer;
!defined('MINI_EXEC') && die('No access.');

use \Pug\Pug;

class Pug extends \Mini\Lib\ViewRenderer
{
	private static $loaded;

	private static $engine;

	public function __construct()
	{
		if (empty(self::$engine)) {
			self::$engine = new Pug(array(
				'prettyprint' => false,
				'extension' => '.pug',
				'basedir' => \Mini\Lib::path('templates')
			));
		}
	}

	public function display()
	{
		return $this->output();
	}

	public function output($_templateName = null)
	{
		$templateFolder = strtolower(str_replace('Mini\\View\\', '', get_class($this->view)));

		$base = \Mini\Lib::path('templates');

		$file = $base . '/' . $templateFolder . '/' . (!empty($_templateName) ? $_templateName : $this->view->template) . '.pug';

		if (!file_exists($file)) {
			$file = $base . '/error/index.pug';
		}

		extract($this->view->vars);

		ob_start();

		include($file);

		$contents = ob_get_clean();

		return self::$engine->render($contents, $this->view->vars);
	}
}

