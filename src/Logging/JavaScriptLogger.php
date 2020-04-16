<?php

namespace EmilMoe\CloudMonitor\Logging;

use EmilMoe\CloudMonitor\Issue;
use EmilMoe\CloudMonitor\Webhook;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as HttpRequest;

class JavaScriptLogger
{
    /**
     * 
     */
    public function write(HttpRequest $request): void
    {
        //Issue::dispatch(new Exception(), 'javascript');

        Webhook::send(
            'error',
            [
                'app' => $this->getApp($request),
                'incident' => $this->getIncident($request),
                'trace' => $this->getTrace($request),
            ]
        );
    }

    /**
     * 
     */
    private function getApp(HttpRequest $request): array
    {
        return [
            'type' => 'javascript',
            'message' => $request->input('error'),
            'line' => $request->input('line'),
            'file' => $request->input('file') ?? '',
            'severity' => '',
            'level' => '',
            'code' => '',
            'class' => $request->input('msg'),
            'method' => '',
            'previous' => '',
            'preview' => '',
            'url' => $request->input('url'),
            'stage' => env('APP_ENV', 'unknown APP_ENV'),
        ];
    }

    /**
     * 
     */
    private function getIncident(HttpRequest $request): array
    {
        return [
            'ip' => Request::ip(),
            'user_agent' => $request->input('user_agent'),
            'user_id' => auth()->check() ? auth()->id() : null,
            'user' => auth()->check() ? auth()->user()->toJson() : null,
            'session' => Request::hasSession() ? Request::session()->all() : '',
            'request' => Request::except(['password', 'password_repeat', 'password_again']),
        ];
    }

    /**
     * 
     */
    private function getTrace(HttpRequest $request): array
    {
        return collect(json_decode($request->input('trace')))->map(function($trace, $index) {
            return [
                'stack_key' => $index,
                'line' => $trace->lineNumber,
                'preview' => explode(PHP_EOL, $trace->source),
                'file' => '',
                'function' => '',
                'class' => '',
                'type' => '',
                'args' => '',
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
