<?php

namespace EmilMoe\CloudMonitor\Logging;

use App\Log;
use Monolog\Formatter\FormatterInterface;
use Exception;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminate\Support\Facades\Request;
use EmilMoe\CloudMonitor\Webhook;

class LogHandler extends AbstractProcessingHandler
{
    /**
     * 
     */
    private $error;

    /**
     * 
     */
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level);
    }

    /**
     * 
     */
    protected function write(array $record): void
    {
        if (! isset($record['context']['exception'])) {
            return;
        }

        $this->error = $record;
        Webhook::send(
            'issue',
            [
                'app' => $this->getApp($record['context']['exception']),
                'incident' => $this->getIncident(),
                'trace' => $this->getTrace($record['context']['exception']),
            ]
        );
    }

    /**
     * 
     */
    private function getApp($e): array
    {
        return [
            'type' => 'log',
            'message' => $e->getMessage() ?? '',
            'line' => $e->getLine() ?? '',
            'file' => str_ireplace(base_path(), '', $e->getFile()) ?? '',
            'severity' => method_exists($e, 'getSeverity') ? $e->getSeverity() : '',
            'level' => $this->error['level_name'],
            'code' => $e->getCode() ?? '',
            'class' => get_class($e) ?? '',
            'original_class' => method_exists($e, 'getOriginalClassName') ? $e->getOriginalClassName() : '',
            'method' => Request::method(),
            'previous' => $e->getPrevious() ?? '',
            'preview' => $this->getPreview($e->getFile(), $e->getLine()),
            'url' => url()->full(),
            'stage' => env('APP_ENV', 'unknown APP_ENV'),
        ];
    }

    /**
     * 
     */
    private function getIncident(): array
    {
        return [
            'ip' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'user_id' => auth()->check() ? auth()->id() : null,
            'user' => auth()->check() ? auth()->user()->toJson() : null,
            'session' => $_SESSION ?? null,
            'request' => Request::except(['password', 'password_repeat', 'password_again']),
        ];
    }

    /**
     * 
     */
    private function getTrace($e): array
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
