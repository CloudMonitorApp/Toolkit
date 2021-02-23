<?php

namespace CloudMonitor\Toolkit\Core\Meta;

class Request 
{
    public function __construct()
    {
        if (! isset($_SERVER['REQUEST_METHOD'])) {
            return;
        }

        $this->method = request()->getMethod();
        $this->url = request()->getUri();
        $this->version = substr($_SERVER['SERVER_PROTOCOL'], strpos($_SERVER['SERVER_PROTOCOL'], '/'));
        $this->remote_address = $_SERVER['REMOTE_ADDR'] ?? '';

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) === true) {
            $this->remote_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) {
            $this->remote_address = $_SERVER['HTTP_X_REAL_IP'];
        }

        $this->encrypted = isset($_SERVER['HTTPS']);
    }
}