<?php
!defined('SERVER_EXEC') && die('No access.');

class View
{
	public $template = 'index';

	public $vars = array();

	public $css;
	public $js;
	public $googlefont;
	public $meta;
	public $static;
	public $pagetitle;

	private static $renderers = array();

	private $renderer;

	public function __construct()
	{
		$renderer = !empty(Config::$viewRenderer) ? Config::$viewRenderer : 'default';

		$this->renderer = self::getRenderer($renderer);

		$this->renderer->link($this);
	}

	private static function loadRenderer($engine = 'default')
	{
		if ($engine === 'default') {
			return true;
		}

		if (!isset(self::$renderers[$engine])) {
			$file = dirname(__FILE__) . '/view-renderers/' . $engine . '.php';

			self::$renderers[$engine] = file_exists($file);

			if (self::$renderers[$engine]) {
				require_once($file);
			}
		}

		return self::$renderers[$engine];
	}

	private static function getRenderer($engine = 'default')
	{
		$classname = 'ViewRenderer';

		if ($engine !== 'default' && self::loadRenderer($engine)) {
			$classname = ucfirst($engine) . 'ViewRenderer';
		}

		$class = new $classname();

		return $class;
	}

	public function display()
	{
		$this->main();

		$this->vars = array_merge(array(
			'css' => $this->css,
			'js' => $this->js,
			'googlefont' => $this->googlefont,
			'meta' => $this->meta,
			'static' => $this->static,
			'pagetitle' => $this->pagetitle
		), $this->vars);

		echo $this->renderer->display();
	}

	public function main()
	{
	}

	public function set($key, $value = null)
	{
		if (is_string($key)) {
			$key = array(
				$key => $value
			);
		}

		$this->vars = array_merge($this->vars, $key);

		return $this;
	}

	public function loadTemplate($templateName, $vars = array())
	{
		$class = new self;
		$class->set($vars);

		return $class->renderer->output($templateName);
	}

	public function includeTemplate($templateName, $vars = array())
	{
		$this->set($vars);

		return $this->renderer->output($templateName);
	}

	public function escape($string)
	{
		return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}

	public function getTemplateFolder()
	{
		$templateFolder = str_replace('View', '', get_class($this));

		return dirname(__FILE__) . '/../templates/' . $templateFolder;
	}

	public function output($_templateName = null)
	{
		return $this->renderer->output($_templateName);
	}
}

class ViewRenderer
{
	protected $view;

	public function link($parent)
	{
		$this->view = $parent;
	}

	public function display()
	{
		$this->view->vars['body'] = $this->output();

		return Lib::output('common/html', $this->view->vars);
	}

	public function output($_templateName = null)
	{
		$templateFolder = strtolower(str_replace('View', '', get_class($this->view)));

		if (empty($templateFolder)) {
			$templateFolder = 'common';
		}

		$base = Config::getBasePath() . '/templates';

		$file = $base . '/' . $templateFolder . '/' . (!empty($_templateName) ? $_templateName : $this->view->template) . '.php';

		if (!file_exists($file)) {
			$file = $base . '/error/index.php';
		}

		extract($this->view->vars);

		ob_start();

		include($file);

		$contents = ob_get_clean();

		return $contents;
	}

	public function escape($string)
	{
		return $this->view->escape($string);
	}

	public function loadTemplate($templateName, $vars = array())
	{
		return $this->view->loadTemplate($templateName, $vars);
	}

	public function includeTemplate($templateName, $vars = array())
	{
		return $this->view->includeTemplate($templateName, $vars);
	}
}
