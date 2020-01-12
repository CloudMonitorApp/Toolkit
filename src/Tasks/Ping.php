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
    public static function before(string $command, Schedule $schedule): Closure
    {
        return function() use($command, $schedule) {
            $event = self::event($command, $schedule);

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
    public static function after(string $command, Schedule $schedule): Closure
    {
        return function() use($command, $schedule) {
            $event = self::event($command, $schedule);

            Webhook::send('task', json_encode([
                'data' => [
                    'command' => $command,
                    'output' => file_get_contents($event->output),
                ],
                'event' => $event->exitCode === 0 ? 'success' : 'failure',
            ]));
        };
    }

    /**
     * 
     */
    private static function event(string $command, Schedule $schedule): Event
    {
       return collect($schedule->events())->first(function($event) use ($command) {
            return $command === self::command($event);
        });
    }

    /**
     * 
     */
    private static function command(Event $event): string
    {
        return substr($event->command, stripos($event->command, "'artisan'") + strlen("'artisan' "));
    }
}