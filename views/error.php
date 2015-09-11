<?php
!defined('SERVER_EXEC') && die('No access.');

class ErrorView extends View
{
	public function display()
	{
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

		return parent::display();
	}
}
