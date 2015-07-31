<?php

!defined('SERVER_EXEC') && die('No access.');

class View
{
	public $template = 'index';

	public $viewname;

	private $vars = array();

	public $pagetitle;
	public $metakeywords;
	public $metadescription;
	public $css;
	public $js;
	public $canonical;

	public function display()
	{
		$this->main();

		$body = $this->output();

		$vars = array_merge(array(
			'body' => $body,
			'pagetitle' => $this->pagetitle,
			'metakeywords' => $this->metakeywords,
			'metadescription' => $this->metadescription,
			'canonical' => $this->canonical,
			'css' => $this->css,
			'js' => $this->js
		), $this->vars);

		echo Lib::output('common/dom', $vars);
	}

	public function main()
	{
	}

	public function set($key, $value = null)
	{
		if (is_string($key) && isset($value)) {
			$key = array(
				$key => $value
			);
		}

		$this->vars = array_merge($this->vars, $key);
	}

	public function loadTemplate($templateName, $vars = array())
	{
		$class = new self;
		$class->set($vars);

		return $class->output($templateName);
	}

	public function includeTemplate($templateName, $vars = array())
	{
		$this->set($vars);

		return $this->output($templateName);
	}

	public function output($_templateName = null)
	{
		$file = dirname(__FILE__) . '/../templates/' . $this->viewname . '/' . (!empty($_templateName) ? $_templateName : $this->template) . '.php';

		if (!file_exists($file)) {
			$file = dirname(__FILE__) . '/../templates/error/index.php';
		}

		extract($this->vars);

		ob_start();

		include($file);

		$contents = ob_get_clean();

		return $contents;
	}
}