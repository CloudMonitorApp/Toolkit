<?php

namespace CloudMonitor\Toolkit\Tasks;

use \Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule as LaravelSchedule;

class Schedule extends LaravelSchedule
{
    /**
     * Add a new Artisan command event to the schedule.
     * 
     * @param  string $command
     * @param  string $token
     * @param  array  $parameters
     * @return Event
     */
    public function command(string $command, string $token, array $parameters = []): Event
    {
        return parent::command($command, $parameters)
            ->before(Ping::before($command, $token, $this))
            ->after(Ping::after($command, $token, $this));
    }
}