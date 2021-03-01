<?php

namespace CloudMonitor\Toolkit\Error\Providers;

use Exception;
use Throwable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Log\Events\MessageLogged;
use CloudMonitor\Toolkit\Core\Transaction;
use CloudMonitor\Toolkit\Core\Facades\CloudMonitor;

class ExceptionsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['events']->listen(MessageLogged::class, function (MessageLogged $log) {
            $this->handleLog($log->level, $log->message, $log->context);
        });
    }

    protected function handleLog($level, $message, $context)
    {
        if (isset($context['exception']) && ($context['exception'] instanceof Exception || $context['exception'] instanceof Throwable)) {
            return $this->reportException($context['exception']);
        }


        if ($message instanceof Exception || $message instanceof Throwable) {
            return $this->reportException($message);
        }

        if ($this->app['cloudmonitor']->isRecording()) {
            CloudMonitor::currentTransaction()
                ->addContext('logs', array_merge(
                    CloudMonitor::currentTransaction()->getContext()['logs']??[],
                    [
                        compact('level', 'message')
                    ]
                ));
        }
    }

    protected function reportException(Throwable $exception)
    {
        if (!CloudMonitor::isRecording()) {
            CloudMonitor::startTransaction(get_class($exception), Transaction::TRANSACTION_EXCEPTION);
        }
        
        CloudMonitor::reportException($exception, false);
        CloudMonitor::currentTransaction()->setResult('error');
    }
}
