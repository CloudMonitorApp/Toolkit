<?php

namespace CloudMonitor\Toolkit\Core\Facades;

use Illuminate\Support\Facades\Facade;

class CloudMonitor extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor()
    {
        return 'cloudmonitor';
    }
}
