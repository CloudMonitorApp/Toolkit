<?php

namespace CloudMonitor\Toolkit\Logging;

use Monolog\Logger;
use CloudMonitor\Toolkit\Issue;
use CloudMonitor\Toolkit\IssueContract;
use Monolog\Handler\AbstractProcessingHandler;

class LogHandler extends AbstractProcessingHandler implements IssueContract
{
    /**
     * @var Exception
     */
    private $exception;

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

        $this->exception = $record['context']['exception'];

        Issue::dispatch($this, 'php', $record);
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

    public function getCode(): string
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
