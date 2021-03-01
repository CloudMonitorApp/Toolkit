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
        return $_SERVER['REQUEST_METHOD'] ?? null;
    }

    private function url(): ?string
    {
        return request()->getUri() ?? null;
    }

    private function protocol(): ?string
    {
        return substr($_SERVER['SERVER_PROTOCOL'], strpos($_SERVER['SERVER_PROTOCOL'], '/')) ?? null;
    }
    
    private function address(): ?string
    {
        $address = $_SERVER['REMOTE_ADDR'];

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) === true) {
            $this->remote_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) {
            $this->remote_address = $_SERVER['HTTP_X_REAL_IP'];
        }

        return $address ?? null;
    }

    private function encrypted(): bool
    {
        return isset($_SERVER['HTTPS']);
    }
}