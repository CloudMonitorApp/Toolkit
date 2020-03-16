<?php

namespace EmilMoe\CloudMonitor\Logging;

use App\Log;
use Monolog\Formatter\FormatterInterface;
use Exception;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminate\Support\Facades\Request;
use EmilMoe\CloudMonitor\Webhook;
use Illuminate\Http\Request as HttpRequest;

class JavaScriptLogger extends AbstractProcessingHandler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * 
     */
    public function write(HttpRequest $request): void
    {
        $this->request = $request;
        Webhook::send('error', $this->getData($record));
    }

    /**
     * 
     */
    private function getApp(): array
    {
        return [
            'type' => 'javascript',
            'message' => $this->request->input('message'),
            'line' => $this->request->input('line'),
            'file' => '',
            'severity' => '',
            'level' => '',
            'code' => '',
            'class' => '',
            'original_class' => '',
            'method' => $this->request->input('method'),
            'previous' => $e->getPrevious() ?? '',
            'preview' => $this->getPreview($e->getFile(), $e->getLine()),
            'url' => $this->request->input('url'),
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
            'user_agent' => Request::userAgent(),
            'user_id' => auth()->check() ? auth()->id() : null,
            'user' => auth()->check() ? auth()->user()->toJson() : null,
            'session' => Request::hasSession() ? Request::session()->all() : '',
            'request' => Request::except(['password', 'password_repeat', 'password_again']),
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
    private function getData(array $e): string
    {
        return json_encode(
            [
                'app' => $this->getApp($e['context']['exception']),
                'incident' => $this->getIncident(),
                'trace' => $this->getTrace($e['context']['exception']),
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
