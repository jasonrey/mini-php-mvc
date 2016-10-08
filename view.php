<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

use Mini\Config;
use Mini\Lib;

abstract class View
{
	private $renderer;

	public function __construct()
	{
		$renderer = !empty(Config::$viewRenderer) ? Config::$viewRenderer : 'v2';

		$classname = '\\Mini\\Lib\\ViewRenderer\\' . ucfirst($renderer);

		$this->renderer = new $classname($this);
	}

	public function display($vars = array())
	{
		$this->set('base', \Mini\Config::getHTMLBase());

		$content = $this->render();

		// Render css
		if (Config::env() === 'development' && !empty(Config::$cssRenderer) && !empty($this->css)) {
			$cssfiles = $this->css;

			if (is_string($cssfiles)) {
				$cssfiles = array($cssfiles);
			}

			foreach ($cssfiles as $css) {
				if (substr($css, 0, 2) === '//' || substr($css, 0, 4) === 'http') {
					continue;
				}

				$response = exec(Lib\Path::resolve('lib/scripts/build.sh') . ' css ' . $css, $output, $result);

				if ($result !== 0) {
					throw(new \Exception($result . ': ' . $response));
				}
			}
		}

		echo $content;
	}

	abstract public function render();

	public function output($template = null, $vars = array())
	{
		$this->set($vars);

		if (empty($template)) {
			$template = strtolower(str_replace('Mini\\View\\', '', get_class($this)));
		}

		return $this->renderer->output($template);
	}

	public function set($key, $value = null)
	{
		$this->renderer->set($key, $value);

		return $this;
	}

	public function get($key)
	{
		return $this->renderer->get($key);
	}
}

abstract class ViewRenderer
{
	public $view;
	public $vars = array();

	public static $extension = 'php';

	public function __construct($view = null)
	{
		if (!empty($view)) {
			$this->link($view);
		}
	}

	public function link($view)
	{
		$this->view = $view;
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

	public function get($key)
	{
		return $this->vars[$key];
	}

	public function getTemplateFile($template)
	{
		$templateFile = Lib\Path::resolve('templates/' . $template . '.' . static::$extension);

		if (!file_exists($templateFile)) {
			throw new \Exception('View Renderer Error: ' . $templateFile . ' file not found.');
		}

		return $templateFile;
	}

	public function output($template)
	{
		$templateFile = $this->getTemplateFile($template);

		extract(array_merge(get_object_vars($this->view), $this->vars));

		ob_start();

		include $templateFile;

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

	public function url($path)
	{
		return \Mini\Config::getHTMLBase() . $path;
	}

	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->view, $method), $arguments);
	}
}
