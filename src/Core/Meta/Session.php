<?php

namespace CloudMonitor\Toolkit\Core\Meta;

class Session 
{
    public function __construct()
    {
        if (! method_exists(request(), 'session') || ! request()->hasSession()) {
            return;
        }

        foreach(request()->session()->all() as $key => $value) {
            $this->{$key} = $value;
        }
    }
}