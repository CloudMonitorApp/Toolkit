<?php

namespace CloudMonitor\Toolkit\Logging;

use CloudMonitor\Toolkit\Issue;
use CloudMonitor\Toolkit\IssueContract;
use Illuminate\Http\Request as HttpRequest;

class JavaScriptLogger implements IssueContract
{
    /**
     * HTTP Request
     * 
     * @var HttpRequest
     */
    private $request;

    /**
     * 
     */
    public function write(HttpRequest $request): void
    {
        $this->request = $request;
        Issue::dispatch($this, 'javascript');
    }

    public function getMessage(): string
    {
        return $this->request->input('error');
    }

    public function getLine(): int
    {
        return $this->request->input('line');
    }

    public function getFile(): string
    {
        return $this->request->input('file') ?? '';
    }

    public function getSeverity(): int
    {
        return 0;
    }

    public function getCode(): string
    {
        return '';
    }

    public function getClass(): string
    {
        return $this->request->input('msg');
    }

    public function getMethod(): string
    {
        return '';
    }

    public function getPrevious(): string
    {
        return '';
    }

    public function getPreview(): array
    {
        return [];
    }

    public function getUrl(): string
    {
        return $this->request->input('url');
    }

    /**
     * 
     */
    public function getTrace(): array
    {
        return collect(json_decode($this->request->input('trace')))->map(function($trace, $index) {
            return [
                'stack_key' => $index,
                'line' => $trace->lineNumber,
                'preview' => explode(PHP_EOL, $trace->source),
                'file' => 'file.js',
                'function' => 'function',
                'class' => 'class',
                'type' => 'type',
                'args' => '',
            ];
        })->toArray();
    }
}
