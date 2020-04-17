<?php

namespace EmilMoe\CloudMonitor\Listeners\Backup;

class BackupWasSuccessful extends BackupListener
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
    public function handle(\Spatie\Backup\Events\BackupWasSuccessful $event)
    {
        $this->dispatch('backup', 200);
    }
}
