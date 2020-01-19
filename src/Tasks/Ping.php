<?php

namespace EmilMoe\CloudMonitor\Tasks;

use \Closure;
use EmilMoe\CloudMonitor\Webhook;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

class Ping
{
    /**
     * [before description]
     * @param  string   $command  [description]
     * @param  Schedule $schedule [description]
     * @return [type]             [description]
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
                    'command' => $command,
                    'cron' => $event->expression,
                    'description' => self::description($command),
                ],
                'event' => 'before',
            ]));
        };
    }

    /**
     * [after description]
     * @param  string   $command  [description]
     * @param  Schedule $schedule [description]
     * @return [type]             [description]
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
     * [event description]
     * @param  string   $command  [description]
     * @param  Schedule $schedule [description]
     * @return [type]             [description]
     */
    private static function event(string $command, Schedule $schedule): Event
    {
       return collect($schedule->events())->first(function($event) use ($command) {
            return $command === self::command($event);
        });
    }

    /**
     * [command description]
     * @param  Event  $event [description]
     * @return [type]        [description]
     */
    private static function command(Event $event): string
    {
        $cmd = str_replace('"', "'", $event->command);
        return substr($cmd, stripos($cmd, "'artisan'") + strlen("'artisan' "));
    }

    /**
     * [description description]
     * @param  string $command [description]
     * @return [type]          [description]
     */
    private static function description(string $command): ?string
    {
        $commands = app()->make(\Illuminate\Contracts\Console\Kernel::class)->all();

        if (! isset($commands[$command])) {
            return null;
        }
        try {
            $className = get_class($commands[$command]);
            $reflection = new \ReflectionClass($className);
            return (string)$reflection->getDefaultProperties()['description'];
        } catch (\ReflectionException $exception) {
            return null;
        }
    }
}