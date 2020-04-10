<?php

namespace EmilMoe\CloudMonitor\Channels;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class BackupWasSuccessful extends CloudMonitorChannel
{
    /**
     * @param Notifiable $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        parent::dispatch($notifiable, $notification, 'backup', 200);
    }
}
