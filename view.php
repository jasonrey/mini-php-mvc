<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

use Mini\Config;
use Mini\Lib;

class View
{
	public $template = 'index';

	public $vars = array();

	public $css = array();
	public $js = array();
	public $googlefont = array();
	public $meta = array();
	public $static = array();
	public $pagetitle = '';

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
		$classname = '\\Mini\\Lib\\ViewRenderer';

		if ($engine !== 'default' && self::loadRenderer($engine)) {
			$classname = $classname . '\\' . ucfirst($engine) . 'ViewRenderer';
		}

		$class = new $classname();

		return $class;
	}

	// v2.0 - Changed to static method
	public static function display($vars = array())
	{
		$view = new static();

		echo $view->render($vars);
	}

	// v2.0 - Ported from original $View->display method
	public function render($vars = array())
	{
		$this->main();

		if (is_string($this->css)) {
			$this->css = array($this->css);
		}

		if (is_string($this->js)) {
			$this->js = array($this->js);
		}

		if (is_string($this->static)) {
			$this->static = array($this->static);
		}

		// Render css
		if (Config::env() === 'development' && !empty(Config::$cssRenderer)) {
			foreach ($this->css as $css) {
				if (substr($css, 0, 4) === 'http') {
					continue;
				}

				$response = exec(Lib::path('lib/scripts/build.sh') . ' css ' . $css, $output, $result);

				if ($result !== 0) {
					throw(new \Exception($result . ': ' . $response));
				}
			}
		}

		foreach ($this->css as &$css) {
			$css = substr($css, 0, 4) === 'http' ? $css : 'assets/css/' . $css . '.css';
		}

		foreach ($this->js as &$js) {
			$js = substr($js, 0, 4) === 'http' ? $js : 'assets/js/' . $js . '.js';
		}

		$this->vars = array_merge(array(
			'css' => $this->css,
			'js' => $this->js,
			'googlefont' => $this->googlefont,
			'meta' => $this->meta,
			'static' => $this->static,
			'pagetitle' => $this->pagetitle,
			'htmlbase' => Config::getHTMLBase(),
			'env' => Config::env()
		), $this->vars, $vars);

		return $this->renderer->display();
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

	public function output($_templateName = null, $vars = array())
	{
		$this->set($vars);

		return $this->renderer->output($_templateName);
	}

	public function addCSS()
	{
		$this->css = array_merge($this->css, func_get_args());

		return $this;
	}

	public function addJS()
	{
		$this->js = array_merge($this->js, func_get_args());

		return $this;
	}

	public function addStatic()
	{
		$this->static = array_merge($this->static, func_get_args());

		return $this;
	}
}

class ViewRenderer
{
	public $view;

	public function link($parent)
	{
		$this->view = $parent;
	}

	public function display()
	{
		$this->view->vars['body'] = $this->output();

		$base = new Lib\View;

		return $base->output('common/html', $this->view->vars);
	}

	public function output($_templateName = null)
	{
		$viewClass = get_class($this->view);

		$templateFolder = '';

		if ($viewClass !== 'Mini\\Lib\\View') {
			$templateFolder = strtolower(str_replace('Mini\\View\\', '', $viewClass)) . '/';
		}

		$base = Lib::path('templates');

		$file = $base . '/' . $templateFolder . (!empty($_templateName) ? $_templateName : $this->view->template) . '.php';


		if (!file_exists($file)) {
			$file = $base . '/error/index.php';
		}

		extract($this->view->vars);

		ob_start();

		include($file);

		$contents = ob_get_clean();

		return $contents;
	}

	public function e($string)
	{
		return $this->escape($string);
	}

	public function escape($string)
	{
		return htmlspecialchars((string) $string, ENT_COMPAT, 'UTF-8');
	}

	public function loadTemplate($templateName, $vars = array())
	{
		$class = new get_class($this->view);
		$class->set($vars);

		return $class->output($templateName);
	}

	public function includeTemplate($templateName, $vars = array())
	{
		$this->view->set($vars);

		return $this->output($templateName);
	}
}
