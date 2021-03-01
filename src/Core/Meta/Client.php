<?php

namespace CloudMonitor\Toolkit\Core\Meta;

use Illuminate\Support\Facades\Request;

class Client 
{
    public function __construct() 
    {
        $this->user_agent = Request::header('User-Agent');
    }
}