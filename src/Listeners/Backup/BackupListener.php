<?php

namespace CloudMonitor\Toolkit\Listeners\Backup;

use CloudMonitor\Toolkit\Webhook;
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
        if (session()->has($event . $code)) {
            return;
        }

        session()->put($event . $code, true);

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
