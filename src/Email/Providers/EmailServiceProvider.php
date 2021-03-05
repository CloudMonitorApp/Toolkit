<?php

namespace CloudMonitor\Toolkit\Email\Providers;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use CloudMonitor\Toolkit\Core\Facades\CloudMonitor;

class EmailServiceProvider extends ServiceProvider
{
    protected $segments = [];

    public function boot()
    {
        $this->app['events']->listen(MessageSending::class, function (MessageSending $event) {
            if (CloudMonitor::isRecording() && $this->app['cloudmonitor']->canAddSegment('email')) {
                $segment = CloudMonitor::startSegment('email', get_class($event->message))
                    ->addContext('data', $event->data);

                if (! $segment) {
                    return;
                }

                $this->segments[$this->getSegmentKey($event->message)] = $segment;
            }
        });

        $this->app['events']->listen(MessageSent::class, function (MessageSent $event) {
            $key = $this->getSegmentKey($event->message);

            if (array_key_exists($key, $this->segments)) {
                $this->segments[$key]->end();
            }
        });
    }

    protected function getSegmentKey(\Swift_Message $message)
    {
        return sha1(trim($message->getHeaders()->get('Content-Type')->toString()));
    }
}
