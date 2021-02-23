<?php

namespace CloudMonitor\Toolkit\Notification\Providers;

use Illuminate\Support\ServiceProvider;
use CloudMonitor\Toolkit\Core\Facades\CloudMonitor;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Events\NotificationSending;

class NotificationServiceProvider extends ServiceProvider
{
    protected $segments = [];

    public function boot()
    {   
        $this->app['events']->listen(NotificationSending::class, function (NotificationSending $event) {
            if ($this->app['cloudmonitor']->isRecording()) {
                $this->segments[$event->notification->id] =
                    CloudMonitor::startSegment('notifications', get_class($event->notification))
                        ->addContext('data', [
                            'Channel' => $event->channel,
                            'Notifiable' => get_class($event->notifiable),
                        ]
                    );
            }
        });

        $this->app['events']->listen(NotificationSent::class, function (NotificationSent $event) {
            if (array_key_exists($event->notification->id, $this->segments)) {
                $this->segments[$event->notification->id]
                    ->addContext('Response', $event->response)
                    ->end();
            }
        });
    }
}
