<?php

namespace CloudMonitor\Toolkit\ScheduledTask\Providers;

use CloudMonitor\Toolkit\Core\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;

class ScheduledTaskServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['events']->listen(ScheduledTaskStarting::class, function (ScheduledTaskStarting $event) {
            $log = storage_path('logs/schedule-'.sha1($event->task->mutexName()).'.log');

            if (is_null($event->task->output) || $event->task->output == $event->task->getDefaultOutput()) {
                $log = storage_path('logs/schedule-'.sha1($event->task->mutexName()).'.log');
                $event->task->sendOutputTo($log);
            }
        });

        $this->app['events']->listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            $output = file_get_contents($event->task->output);

            dispatch((new Queue([
                'type' => 'output',
                'uuid' => str_ireplace('#CLOUDMONITOR#', '', strtok($output, "\n")),
                'context' => trim(preg_replace('/^.+\n/', '', $output), "\n"),
                'task' => $event->task,
                'result' => $event->task->exitCode === 0 ? 'success' : 'error',
            ]))->delay(2));

            //unlink(storage_path('logs/schedule-'.sha1($event->task->mutexName()).'.log'));
        });

        $this->app['events']->listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event) {
            $output = file_get_contents($event->task->output);

            dispatch((new Queue([
                'type' => 'output',
                'uuid' => str_ireplace('#CLOUDMONITOR#', '', strtok($output, "\n")),
                'context' => trim(preg_replace('/^.+\n/', '', $output), "\n"),
                'result' => 'error',
            ]))->delay(2));
        });
    }
}