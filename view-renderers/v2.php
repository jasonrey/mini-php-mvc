<?php namespace Mini\Lib\ViewRenderer;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib;

class V2 extends \Mini\Lib\ViewRenderer
{
	private $parent;

	private $blocks = array();
	private $currentBlock;

	public function using($parent, $vars = array())
	{
		if (!empty($this->parent)) {
			throw new \Exception('View Renderer Error: Extend parent template already declared: ' . $this->parent . '. You can only declare 1 parent template to extend.');
		}

		$this->parent = $parent;

		$this->set($vars);
	}

	public function extending($parent, $vars = array())
	{
		return $this->using($parent, $vars);
	}

	public function isUsingLayout()
	{
		return !empty($this->parent);
	}

	public function start($block)
	{
		if (!empty($this->currentBlock)) {
			throw new \Exception('View Renderer Error: Unclosed block found: . ' . $this->currentBlock . '. You must stop before starting a block.');
		}

		$this->currentBlock = $block;

		ob_start();
	}

	public function stop()
	{
		$this->blocks[$this->currentBlock] = ob_get_clean();

		$this->currentBlock = null;
	}

	public function block($name)
	{
		if ($this->hasBlock($name)) {
			echo $this->blocks[$name];
		}
	}

	public function hasBlock($name)
	{
		return !empty($this->blocks[$name]);
	}

	public function linkBlocks($blocks)
	{
		$this->blocks = array_merge($this->blocks, $blocks);
	}

	public function output($template)
	{
		$content = parent::output($template);

		$parentContent = '';

		if ($this->isUsingLayout()) {
			if ($template === $this->parent) {
				throw new \Exception('View Renderer Error: Template "' . $template . '" recursion.');
			}

			ob_start();

			$this->includes($this->parent, $this->vars);

			$parentContent = ob_get_clean();
		}

		return $parentContent . $content;
	}

	public function includes($template, $vars = array())
	{
		$renderer = new static($this->view);

		$renderer->set(array_merge($this->vars, $vars));
		$renderer->linkBlocks($this->blocks);

		echo $renderer->output($template);
	}

	public function insert($template, $vars = array())
	{
		return $this->includes($template, $vars);
	}
}
