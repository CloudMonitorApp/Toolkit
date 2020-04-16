<?php

namespace EmilMoe\CloudMonitor\Exceptions;

use Throwable;
use EmilMoe\CloudMonitor\Issue;
use App\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Report unhandled exceptions.
     * Ignored exception from config will not be reported.
     * 
     * @param  Throwable $exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
        Issue::dispatch($exception, 'php');
    }
}