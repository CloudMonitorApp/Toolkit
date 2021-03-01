<?php

namespace CloudMonitor\Toolkit\Core\Meta;

class User
{
    public function __construct()
    {
        $this->id = $this->id();
        $this->name = $this->name();
    }

    private function id(): ?int
    {
        return auth()->id() ?? null;
    }

    private function name(): ?string
    {
        return auth()->user()->{config('cloudmonitor.user_name_field')} ?? null;
    }
}