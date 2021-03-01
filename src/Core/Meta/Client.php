<?php

namespace CloudMonitor\Toolkit\Core\Meta;

class Client 
{
    public function __construct() 
    {
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    }
}