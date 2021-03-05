<?php

namespace CloudMonitor\Toolkit\Redis\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Redis\Events\CommandExecuted;
use CloudMonitor\Toolkit\Core\Facades\CloudMonitor;

class RedisServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['events']->listen(CommandExecuted::class, function (CommandExecuted $event) {
            if (CloudMonitor::isRecording() && $this->app['cloudmonitor']->canAddSegment('redis')) {
                $microtimeDuration = $event->time / 1000;

                $segment = CloudMonitor::startSegment('redis', "redis:{$event->command}");

                if (! $segment) {
                    return;
                }

                $segment->start(microtime(true) - $microtimeDuration)
                    ->addContext('data', [
                        'connection' => $event->connectionName,
                        'parameters' => $event->parameters
                    ])
                    ->end($microtimeDuration);
            }
        });

        foreach ((array) $this->app['redis']->connections() as $connection) {
            $connection->setEventDispatcher($this->app['events']);
        }

        $this->app['redis']->enableEvents();
    }
}
