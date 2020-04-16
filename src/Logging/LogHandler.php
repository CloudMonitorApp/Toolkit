<?php

namespace EmilMoe\CloudMonitor\Logging;

use Monolog\Logger;
use EmilMoe\CloudMonitor\Issue;
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
