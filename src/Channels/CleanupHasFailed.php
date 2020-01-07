<?php

namespace EmilMoe\CloudMonitor\Channels;

use App\Exceptions\WebHookFailedException;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class CleanupHasFailed extends CloudMonitorChannel
{
    /**
     * @param Notifiable $notifiable
     * @param Notification $notification
     * @throws WebHookFailedException
     */
    public function send($notifiable, Notification $notification)
    {
        Log::info(json_encode($this->exception));
        parent::dispatch($notifiable, $notification, 'cleanup', 100);
    }
}
