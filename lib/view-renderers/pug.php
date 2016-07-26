<?php
!defined('SERVER_EXEC') && die('No access.');

class PugViewRenderer extends ViewRenderer
{
	private static $loaded;

	private static $engine;

	public function __construct()
	{
		if (empty(self::$engine)) {
			self::$engine = new Pug\Pug(array(
				'prettyprint' => false,
				'extension' => '.pug',
				'basedir' => Config::getBasePath() . '/templates'
			));
		}
	}

	public function display()
	{
		return $this->output();
	}

	public function output($_templateName = null)
	{
		$templateFolder = strtolower(str_replace('View', '', get_class($this->view)));

		$base = Config::getBasePath() . '/templates';

		$file = $base . '/' . $templateFolder . '/' . (!empty($_templateName) ? $_templateName : $this->view->template) . '.pug';

		if (!file_exists($file)) {
			$file = $base . '/error/index.pug';
		}

		return self::$engine->render(file_get_contents($file), $this->view->vars);
	}
}

