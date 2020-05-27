<?php

namespace CloudMonitor\Toolkit\Logging;

use Monolog\Logger;
use CloudMonitor\Toolkit\Issue;
use Monolog\Handler\AbstractProcessingHandler;

class LogHandler extends AbstractProcessingHandler
{
    /**
     * 
     */
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level);
    }

    /**
     * 
     */
    protected function write(array $record): void
    {
        if (! isset($record['context']['exception'])) {
            return;
        }

        Issue::dispatch($record['context']['exception'], 'php', $record);
    }
}
