<?php

namespace EmilMoe\CloudMonitor\Listeners\Backup;

use EmilMoe\CloudMonitor\Webhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

abstract class BackupListener
{
    /**
     * Send event to CloudMonitor.
     * 
     * @param  string  $event
     * @param  int     $code
     * @param  int     $message
     */
    protected function dispatch(string $event, int $code, string $message = null): void
    {
        Webhook::send(
            'backup',
            [
                'event'   => $event,
                'code'    => $code,
                'message' => $message,
            ]
        );
    }
}
