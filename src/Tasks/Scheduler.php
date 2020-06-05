<?php

namespace CloudMonitor\Toolkit\Tasks;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

class Scheduler
{
    /**
     * Add a new Artisan command event to the schedule.
     * 
     * @param  string $command
     * @param  string $token
     * @param  array  $parameters
     * @return Event
     */
    public static function command(string $command, string $token, array $parameters = []): Event
    {
        $scheduler = null;

        app()->booted(function() use($command, $token, $parameters, &$scheduler) {
            $schedule = app()->make(Schedule::class);

            $scheduler = $schedule->command($command, $parameters)
                ->before(Ping::before($command, $token, $schedule))
                ->after(Ping::after($command, $token, $schedule));
        });

        return $scheduler;
    }
}