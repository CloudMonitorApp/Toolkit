<?php

namespace CloudMonitor\Toolkit\Core\Meta;

class User
{
    public function __construct()
    {
        if (! auth()->check()) {
            return;
        }

        $this->id = auth()->id();
        $this->name = auth()->user()->{config('cloudmonitor.user_name_field')};
    }
}