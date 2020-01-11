<?php

namespace EmilMoe\CloudMonitor\Tasks;

use \Closure;
use EmilMoe\CloudMonitor\Webhook;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

class Ping
{
    /**
     * 
     */
    public static function before(Schedule $schedule): Closure
    {
        return function() use($schedule) {
            $event = self::event($schedule);

            if (is_null($event->output) || $event->output == $event->getDefaultOutput()) {
                $event->sendOutputTo(storage_path('logs/schedule-'.sha1($event->mutexName()).'.log'));
            }

            Webhook::send('task', json_encode([
                'data' => [
                    'command' => self::command($event),
                    'cron' => $event->expression,
                    'description' => $event->description,
                ],
                'event' => 'before',
            ]));
        };
    }

    /**
     * 
     */
    public static function after(Schedule $schedule): Closure
    {
        return function() use($schedule) {
            $event = self::event($schedule);

            Webhook::send('task', json_encode([
                'data' => [
                    'command' => self::command($event),
                    'output' => $event->exitCode === 0 ? '' : @file_get_contents($event->output),
                ],
                'event' => $event->exitCode === 0 ? 'success' : 'failure',
            ]));
        };

        #return function() {};
    }

    /**
     * 
     */
    private static function event(Schedule $schedule): Event
    {
        return $schedule->events()[0];
    }

    /**
     * 
     */
    private static function command(Event $event): string
    {
        return substr($event->command, stripos($event->command, "'artisan'") + strlen("'artisan' "));
    }
}