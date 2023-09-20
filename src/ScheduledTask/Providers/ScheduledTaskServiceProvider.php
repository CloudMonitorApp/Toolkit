<?php

namespace CloudMonitor\Toolkit\ScheduledTask\Providers;

use CloudMonitor\Toolkit\Core\Queue;
use Illuminate\Support\ServiceProvider;
use CloudMonitor\Toolkit\Core\Transaction;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;

class ScheduledTaskServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['events']->listen(ScheduledTaskStarting::class, function (ScheduledTaskStarting $event) {
            if (is_null($event->task->output) || $event->task->output == $event->task->getDefaultOutput()) {
                $log = storage_path('cloudmonitor/'. $this->command($event) .'.output');
                $event->task->sendOutputTo($log);
            }

            $t = (new Transaction($this->command($event), Transaction::TRANSACTION_TASK))->addContext('task', [
                'type' => Transaction::TRANSACTION_TASK,
                'command' => $this->command($event),
                'uuid' => '',
                'expression' => $event->task->expression,
                'state' => 'start',
            ]);

            dispatch(new Queue($t, 'start-schedule'));
        });

        $this->app['events']->listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            echo $this->command($event);
            $this->finish($event);
        });

        $this->app['events']->listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event) {
            $this->finish($event);
        });
    }

    private function finish($event)
    {
        $output = null;

        if (is_file($event->task->output)) {
            $output = file_get_contents($event->task->output);
        }

        $t = (new Transaction($this->command($event), Transaction::TRANSACTION_TASK))->addContext('task', [
            'type' => Transaction::TRANSACTION_TASK,
            'command' => $this->command($event),
            'context' => trim(preg_replace('/^.+\n/', '', $output), "\n"),
            'expression' => $event->task->expression,
            'result' => $event->task->exitCode,
        ]);

        $t->uuid = str_ireplace('#CLOUDMONITOR#', '', strtok($output, "\n"));

        dispatch(new Queue($t, 'end-schedule'));

        @unlink(storage_path('cloudmonitor/'. $this->command($event) .'.output'));
    }

    private function command($event): string
    {
        return substr($event->task->command, strpos($event->task->command, "'artisan' ") + 10);
    }
}