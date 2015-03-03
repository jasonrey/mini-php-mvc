<?php

!defined('SERVER_EXEC') && die('No access.');

abstract class View
{
    public $template = 'index';

    public $viewname;

    private $vars = array();

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
        extract($this->vars);

        ob_start();

        include(__DIR__ . '/../templates/' . $this->viewname . '/' . (!empty($_templateName) ? $_templateName : $this->template) . '.php');

        $contents = ob_get_clean();

        return $contents;
    }
}