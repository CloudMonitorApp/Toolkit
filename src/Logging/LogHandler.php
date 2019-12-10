<?php

namespace EmilMoe\CloudMonitor\Logging;

use App\Log;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminate\Support\Facades\Request;
use EmilMoe\CloudMonitor\Webhook;
use Illuminate\Encryption\Encrypter;

class LogHandler extends AbstractProcessingHandler
{
    private $error;

    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level);
    }

    protected function write(array $record): void
    {
        $this->error = $record;

        Webhook::send($this->getData($record));
    }

    private function getApp(Exception $e): array
    {
        return [
            'type' => 'php',
            'message' => $e->getMessage() ?? '',
            'line' => $e->getLine() ?? '',
            'file' => $e->getFile() ?? '',
            'severity' => $e->getSeverity() ?? '',
            'level' => $this->error['level_name'],
            'code' => $e->getCode() ?? '',
            'class' => get_class($e) ?? '',
            'original_class' => $e->getOriginalClassName() ?? '',
            'method' => Request::method(),
            'previous' => $e->getPrevious() ?? '',
            'preview' => $this->getPreview($e->getFile(), $e->getLine()),
            'url' => url()->full(),
            'stage' => env('APP_ENV', 'unknown APP_ENV'),
        ];
    }

    private function getIncident(): array
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
                'file' => $trace['file'] ?? null,
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
    private function getData(array $e): string
    {
        $encrypter = new Encrypter(base64_decode(env('CLOUDMONITOR_SECRET')), 'AES-128-CBC');

        return $encrypter->encrypt(
            json_encode(
                [
                    'app' => $this->getApp($e['context']['exception']),
                    'incident' => $this->getIncident(),
                    'trace' => $this->getTrace($e['context']['exception']),
                ]
            )
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
