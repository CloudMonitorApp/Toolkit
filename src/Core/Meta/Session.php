<?php

namespace CloudMonitor\Toolkit\Core\Meta;

class Session 
{
    public function __construct()
    {
        if (! isset($_SERVER['REQUEST_METHOD'])) {
            return;
        }

        foreach(request()->session()->all() as $key => $value) {
            $this->{$key} = $value;
        }
    }
}