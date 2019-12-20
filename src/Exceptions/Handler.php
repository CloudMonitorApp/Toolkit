<?php

namespace EmilMoe\CloudMonitor\Exceptions;

use App\Exceptions\Handler as ExceptionHandler;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use EmilMoe\CloudMonitor\Webhook;

class Handler extends ExceptionHandler
{
    public function report(Exception $e)
    {
        parent::report($e);
        Webhook::send('error', $this->getData($e));
    }

    private function getApp(Exception $e): array
    {
        return [
            'type' => 'php',
            'message' => $e->getMessage() ?? '',
            'line' => $e->getLine() ?? '',
            'file' => str_ireplace(base_path(), '', $e->getFile()) ?? '',
            'severity' => $e instanceof \Exception ? 0 : $e->getSeverity() ?? '',
            'level' => '',
            'code' => $e->getCode() ?? '',
            'class' => get_class($e) ?? '',
            'original_class' => $e instanceof \Exception ? 'Exception' : $e->getOriginalClassName() ?? '',
            'method' => Request::method(),
            'previous' => $e->getPrevious() ?? '',
            'preview' => $this->getPreview($e->getFile(), $e->getLine()),
            'url' => url()->full(),
            'stage' => env('APP_ENV', 'unknown APP_ENV'),
        ];
    }

    private function getIncident(Exception $e): array
    {
        return [
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'user_id' => auth()->check() ? auth()->id() : null,
            'user_data' => auth()->check() ? auth()->user()->toJson() : null,
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
                'args' => $trace['args'],
                'preview' => isset($trace['file'], $trace['line']) ? $this->getPreview($trace['file'], $trace['line']) : null,
            ];
        })->toArray();
    }

    /**
     * @param Exception $e
     * @return string
     */
    private function getData(Exception $e): string
    {
        return json_encode(
            [
                'app' => $this->getApp($e),
                'incident' => $this->getIncident($e),
                'trace' => $this->getTrace($e),
            ]
        );
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