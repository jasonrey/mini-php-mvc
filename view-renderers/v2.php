<?php
!defined('MINI_EXEC') && die('No access.');

class V2ViewRenderer extends ViewRenderer
{
	private $parent;
	private $child;

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
	}

	public function block($name)
	{
		if ($this->hasBlock($name)) {
			echo $child->blocks[$name];
		}
	}

	public function hasBlock($name)
	{
		return $this->hasChild() && isset($child->blocks[$name]);
	}

	public function hasChild()
	{
		return isset($this->child);
	}

	public function link($child)
	{
		$this->child = $child;
	}

	public function render()
	{
		// Get the layout first


		if (!empty($this->parent)) {
			$parent = new self($this->view);

			$parent->set($this->vars);

			$parent->link($this);

			$parentLayout = $parent->render($this->parent);
		}
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
