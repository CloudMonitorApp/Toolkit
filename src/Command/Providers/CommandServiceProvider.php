<?php

namespace CloudMonitor\Toolkit\Command\Providers;

use ReflectionClass;
use ReflectionException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Console\Kernel;
use CloudMonitor\Toolkit\Core\Transaction;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Input\ArgvInput;

class CommandServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! $this->isApproved()) {
            return;
        }

        if (!$this->app['cloudmonitor']->isRecording()) {
            $this->app['cloudmonitor']->startTransaction(str_replace('artisan ', '', implode(' ', $_SERVER['argv'])), Transaction::TRANSACTION_COMMAND);
            echo '#CLOUDMONITOR#'. $this->app['cloudmonitor']->transaction->uuid . PHP_EOL;
        }

        $this->app['events']->listen(CommandStarting::class, function(CommandStarting $event) {
            //
        });

        $this->app['events']->listen(CommandFinished::class, function (CommandFinished $event) {
            if($this->app['cloudmonitor']->isRecording()) {
                $this->app['cloudmonitor']->currentTransaction()
                    ->addContext('Command', [
                        'exit_code' => $event->exitCode,
                        'arguments' => $event->input->getArguments(),
                        'description' => $this->description($event->input->getArguments()['command']),
                        'options' => $event->input->getOptions(),
                    ])
                    ->setResult($event->exitCode === 0 ? 'success' : 'error');
            }
        });
    }

    private function isApproved(): bool
    {
        return ! in_array(
            (new ArgvInput)->getFirstArgument(),
            array_merge(
                config('cloudmonitor.ignored_commands'),
                [null]
            )
        );
    }

    private function description(string $command): ?string
    {
        $commands = app()->make(Kernel::class)->all();

        if (! isset($commands[$command])) {
            return null;
        }
        try {
            $className = get_class($commands[$command]);
            $reflection = new ReflectionClass($className);

            return (string) $reflection->getDefaultProperties()['description'] ?? '';
        } catch (ReflectionException $exception) {
            return null;
        }
    }
}
