<?php

namespace CloudMonitor\Toolkit\Core\Meta;

class Session 
{
    public function __construct()
    {
        foreach(request()->session()->all() as $key => $value) {
            $this->{$key} = $value;
        }
    }
}