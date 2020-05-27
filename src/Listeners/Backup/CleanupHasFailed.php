<?php

namespace CloudMonitor\Toolkit\Listeners\Backup;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CleanupHasFailed extends BackupListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(\Spatie\Backup\Events\CleanupHasFailed $event)
    {
        $this->dispatch('cleanup', 100, $event->exception->getMessage());
    }
}
