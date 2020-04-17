<?php

namespace EmilMoe\CloudMonitor\Listeners\Backup;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HealthyBackupWasFound extends BackupListener
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
    public function handle(\Spatie\Backup\Events\HealthyBackupWasFound $event)
    {
        $this->dispatch('healthy', 200);
    }
}
