<?php

namespace CloudMonitor\Toolkit\Core\Meta;

class Request 
{
    public function __construct()
    {
        $this->method = $this->method();
        $this->url = $this->url();
        $this->version = $this->protocol();
        $this->remote_address = $this->address();
        $this->encrypted = $this->encrypted();
    }

    private function method(): ?string
    {
        if (! isset($_SERVER['REQUEST_METHOD'])) {
            return null;
        }

        return $_SERVER['REQUEST_METHOD'];
    }

    private function url(): ?string
    {
        if (! method_exists(request(), 'getUri')) {
            return null;
        }

        return request()->getUri();
    }

    private function protocol(): ?string
    {
        if (! isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['SERVER_PROTOCOL'])) {
            return null;
        }

        return substr($_SERVER['SERVER_PROTOCOL'], strpos($_SERVER['SERVER_PROTOCOL'], '/'));
    }
    
    private function address(): ?string
    {
        $address = $_SERVER['REMOTE_ADDR'] ?? null;

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) === true) {
            $address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) {
            $address = $_SERVER['HTTP_X_REAL_IP'];
        }

        return $address;
    }

    private function encrypted(): bool
    {
        return isset($_SERVER['HTTPS']);
    }
}