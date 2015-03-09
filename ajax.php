<?php

!defined('SERVER_EXEC') && die('No access.');

class Ajax
{
    public static $instance;

    public static function init()
    {
        if (empty(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function fail($msg = '')
    {
        $response = array(
            'status' => 'fail',
            'response' => $msg
        );

        return $this->send($response);
    }

    public function success($msg = '')
    {
        $response = array(
            'status' => 'success',
            'response' => $msg
        );

        return $this->send($response);
    }

    public function send($msg = '')
    {
        if (is_object($msg) || is_array($msg)) {
            $msg = json_encode($msg);
        }

        echo $msg;
        exit;
    }
}