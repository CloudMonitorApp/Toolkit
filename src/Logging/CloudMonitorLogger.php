<?php

namespace EmilMoe\CloudMonitor\Logging;

use Monolog\Logger;

class CloudMonitorLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('cloudmonitor');
        $logger->pushHandler(new LogHandler());

        return $logger;
    }
}
