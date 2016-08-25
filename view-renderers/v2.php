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
		// Get the layout first
		$templateFile = Lib::path('templates/' . $template . '.php');

		if (!file_exists($templateFile)) {
			throw new \Exception('View Renderer Error: ' . $templateFile . ' file not found.');
		}

		extract($this->vars);

		ob_start();

		include $templateFile;

		$content = ob_get_clean();

		$parentContent = '';

		if ($this->isUsingLayout()) {
			if ($template === $this->parent) {
				throw new \Exception('View Renderer Error: ' . $templateFile . ' recursion.');
			}

			$parent = new static($this->view);

			$parent->set($this->vars);
			$parent->linkBlocks($this->blocks);

			$parentContent = $parent->output($this->parent);
		}

		return $parentContent . $content;
	}
}

class V2ViewRendererItem
{

}

/*
template/foo/index.php

<?php $this->using('common/view');

$this->start('body');
?>
html codes
<?php $this->stop();

// common/view

<?php $this->using('common/html');

$this->start('content');
	$this->block('body');
$this->stop();

// common/html

$this->block('content');


$view = new View\Foo();

echo $view->render(); // -> viewRenderer->Render()

OR

echo View\Index::display();
*/
