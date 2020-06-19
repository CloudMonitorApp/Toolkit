<?php

namespace CloudMonitor\Toolkit\Exceptions;

use Throwable;
use CloudMonitor\Toolkit\Issue;
use CloudMonitor\Toolkit\IssueContract;
use Illuminate\Support\Facades\Request;
use App\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler implements IssueContract
{
    /**
     * @var Throwable
     */
    private $exception;

    /**
     * Report unhandled exceptions.
     * Ignored exception from config will not be reported.
     * 
     * @param  Throwable $exception
     */
    public function report(Throwable $exception)
    {
        $this->exception = $exception;
        parent::report($exception);
        Issue::dispatch($this, 'php');
    }

    public function getMessage(): string
    {
        return $this->exception->getMessage();
    }

    public function getLine(): int
    {
        return $this->exception->getLine();
    }

    public function getFile(): string
    {
        return str_ireplace(base_path(), '', $this->exception->getFile());
    }

    public function getSeverity(): int
    {
        return method_exists($this->exception, 'getSeverity') ? $this->exception->getSeverity() : 0;
    }

    public function getCode(): int
    {
        return $this->exception->getCode();
    }

    public function getClass(): string
    {
        return get_class($this->exception);
    }

    public function getMethod(): string
    {
        return method_exists('Request', 'method') ? Request::method() ?? '' : '';
    }

    public function getPrevious(): string
    {
        return $this->exception->getPrevious() ?? '';
    }

    public function getPreview(): array
    {
        return Issue::makePreview($this->exception->getFile(), $this->exception->getLine());
    }
    
    public function getUrl(): string
    {
        return url()->full();
    }

    public function getTrace(): array
    {
        return collect($this->exception->getTrace())->map(function ($trace, $index) {
            return [
                'stack_key' => $index,
                'file'      => str_ireplace(base_path(), '', $trace['file'] ?? null) ?? null,
                'line'      => $trace['line'] ?? null,
                'function'  => $trace['function'] ?? null,
                'class'     => $trace['class'] ?? null,
                'type'      => $trace['type'] ?? null,
                'args'      => $trace['args'] ?? null,
                'preview'   => isset($trace['file'], $trace['line']) ? Issue::makePreview($trace['file'], $trace['line']) : null,
            ];
        })->toArray();
    }
}