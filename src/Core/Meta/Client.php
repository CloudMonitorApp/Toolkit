<?php

namespace CloudMonitor\Toolkit\Core\Meta;

class Client 
{
    public function __construct() 
    {
        if (! isset($_SERVER['HTTP_USER_AGENT'])) {
            return;
        }
        
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
    }
}