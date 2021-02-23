<?php

namespace CloudMonitor\Toolkit\Core\Middleware;

use Closure;
use CloudMonitor\Toolkit\Core\Facades\CloudMonitor;
use CloudMonitor\Toolkit\Core\Transaction;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\TerminableInterface;

class Web implements TerminableInterface
{
    public function handle($request, Closure $next)
    {
        if (! CloudMonitor::isRecording()) {
            $this->startTransaction($request, 'performance');
        }

        return $next($request);
    }

    protected function startTransaction()
    {
        CloudMonitor::startTransaction(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), Transaction::TRANSACTION_REQUEST);
    }

    public function terminate($request, $response)
    {
        if (CloudMonitor::isRecording()) {
            CloudMonitor::currentTransaction()->setResult($response->getStatusCode());
        }
    }
}
