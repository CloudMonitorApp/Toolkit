<?php

namespace EmilMoe\CloudMonitor\Exceptions;

use App\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use EmilMoe\CloudMonitor\Webhook;
use Exception;

class Handler extends ExceptionHandler
{
    /**
     * Report unhandled exceptions.
     * Ignored exception from config will not be reported.
     */
    public function report(Exception $exception)
    {
        parent::report($exception);

        if($this->isIgnored($exception)) {
            return;
        }
        
        Webhook::send(
            'error',
            [
                'app' => $this->getApp($exception),
                'incident' => $this->getIncident($exception),
                'trace' => $this->getTrace($exception),
            ]
        );
    }

    /**
     * Check if reported exception should be ignored.
     * 
     * @param Exception $t
     * @return bool
     */
    private function isIgnored(Exception $t): bool
    {
        return collect(config('cloudmonitor.exceptions.ignore'))->contains(function(string $class) use($t) {
            return $t instanceof $class;
        });
    }

    /**
     * 
     */
    private function getApp(Exception $t): array
    {
        return [
            'type' => 'php',
            'message' => $t->getMessage() ?? '',
            'line' => $t->getLine() ?? '',
            'file' => str_ireplace(base_path(), '', $t->getFile()) ?? '',
            'severity' => method_exists($t, 'getSeverity') ? $t->getSeverity() : '',
            'level' => '',
            'code' => $t->getCode() ?? '',
            'class' => get_class($t) ?? '',
            'method' => Request::method(),
            'previous' => $t->getPrevious() ?? '',
            'preview' => $this->getPreview($t->getFile(), $t->getLine()),
            'url' => app()->runningInConsole() ? 'Console' : url()->full(),
            'stage' => env('APP_ENV', 'unknown APP_ENV'),
        ];
    }

    private function getIncident(Exception $e): array
    {
        return [
            'ip' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'user_id' => auth()->check() ? auth()->id() : null,
            'user' => auth()->check() ? auth()->user()->toJson() : null,
            'session' => $_SESSION ?? null,
            'request' => json_encode(Request::except(['password', 'password_repeat', 'password_again'])),
        ];
    }

    private function getTrace(Exception $e): array
    {
        return collect($e->getTrace())->map(function ($trace, $index) {
            return [
                'stack_key' => $index,
                'file' => str_ireplace(base_path(), '', $trace['file'] ?? null) ?? null,
                'line' => $trace['line'] ?? null,
                'function' => $trace['function'],
                'class' => $trace['class'] ?? null,
                'type' => $trace['type'] ?? null,
                'args' => $trace['args'] ?? null,
                'preview' => isset($trace['file'], $trace['line']) ? $this->getPreview($trace['file'], $trace['line']) : null,
            ];
        })->toArray();
    }

    /**
     * Gather preview of source error.
     *
     * @param string $file
     * @param int $line
     * @return string
     */
    private function getPreview(string $file, int $line): array
    {
        $file = explode(PHP_EOL, file_get_contents($file));
        array_unshift($file, '');
        unset($file[0]);

        $firstLine = $line - 15;

        if ($line <= 0) {
            $firstLine = 0;
        }

        return array_slice($file, $firstLine, 30, true);
    }
}