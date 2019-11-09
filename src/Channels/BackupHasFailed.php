<?php

namespace EmilMoe\CloudMonitor\Channels;

use App\Exceptions\WebHookFailedException;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class BackupHasFailed extends CloudMonitorChannel
{
    /**
     * @param Notifiable $notifiable
     * @param Notification $notification
     * @throws WebHookFailedException
     */
    public function send($notifiable, Notification $notification)
    {
        parent::dispatch($notifiable, $notification, 'backup', 100);
    }
}