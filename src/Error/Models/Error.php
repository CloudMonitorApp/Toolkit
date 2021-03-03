<?php

namespace CloudMonitor\Toolkit\Error\Models;

use Throwable;
use CloudMonitor\Toolkit\Core\Transaction;

class Error
{
    public $model;
    public $language;

    /**
     * Error constructor.
     *
     * @param Throwable $throwable
     * @param Transaction $transaction
     */
    public function __construct(Throwable $throwable, Transaction $transaction)
    {
        $this->model = 'error';
        $this->language = 'php';
        $this->timestamp = microtime(true);

        $this->message = $throwable->getMessage()
            ? $throwable->getMessage()
            : get_class($throwable);

        $this->class = get_class($throwable);
        $this->file = $throwable->getFile();
        $this->line = $throwable->getLine();
        $this->code = $throwable->getCode();

        $this->stack = $this->stackTraceToArray(
            $throwable->getTrace(),
            $throwable->getFile(),
            $throwable->getLine()
        );

        $this->transaction = collect($transaction)->only(['name', 'hash'])->toArray();
    }

    /**
     * Determine if the exception is handled/unhandled.
     *
     * @param bool $value
     * @return $this
     */
    public function setHandled(bool $value)
    {
        $this->handled = $value;
        return $this;
    }

    /**
     * Serialize stack trace to array
     *
     * @param array $stackTrace
     * @param null|string $topFile
     * @param null|string $topLine
     * @return array
     */
    public function stackTraceToArray(array $stackTrace, $topFile = null, $topLine = null)
    {
        $stack = [];
        $counter = 0;

        foreach ($stackTrace as $trace) {
            if (isset($topFile, $topLine) && $topFile && $topLine) {
                $trace['file'] = $topFile;
                $trace['line'] = $topLine;

                unset($topFile, $topLine);
            }

            $stack[] = [
                'class' => isset($trace['class']) ? $trace['class'] : null,
                'function' => isset($trace['function']) ? $trace['function'] : null,
                'args' => $this->stackTraceArgsToArray($trace),
                'type' => $trace['type'] ?? 'function',
                'file' => str_ireplace(base_path(), '', $trace['file'] ?? '[internal]'),
                'line' => $trace['line'] ?? '0',
                'preview' => isset($trace['file']) ? $this->getCode($trace['file'], $trace['line'] ?? '0') : [],
            ];

            $counter++;

            if ($counter >= 50) {
                break;
            }
        }

        return $stack;
    }

    /**
     * Serialize stack trace function arguments
     *
     * @param array $trace
     * @return array
     */
    protected function stackTraceArgsToArray(array $trace)
    {
        $params = [];

        if (!isset($trace['args'])) {
            return $params;
        }

        foreach ($trace['args'] as $arg) {
            if (is_array($arg)) {
                $params[] = 'array(' . count($arg) . ')';
            } else if (is_object($arg)) {
                $params[] = get_class($arg);
            } else if (is_string($arg)) {
                $params[] = 'string(' . $arg . ')';
            } else if (is_int($arg)) {
                $params[] = 'int(' . $arg . ')';
            } else if (is_float($arg)) {
                $params[] = 'float(' . $arg . ')';
            } else if (is_bool($arg)) {
                $params[] = 'bool(' . ($arg ? 'true' : 'false') . ')';
            } else if ($arg instanceof \__PHP_Incomplete_Class) {
                $params[] = 'object(__PHP_Incomplete_Class)';
            } else {
                $params[] = gettype($arg);
            }
        }

        return $params;
    }

    /**
     * Extract code source from file.
     *
     * @param $filePath
     * @param $line
     * @param int $linesAround
     * @return mixed
     */
    public function getCode($filePath, $line = 0, $linesAround = 10)
    {
        $file = explode(PHP_EOL, file_get_contents($filePath));
        array_unshift($file, '');
        unset($file[0]);

        $firstLine = $line - $linesAround;

        if ($firstLine < 0) {
            $firstLine = 0;
        }

        if ($line <= 0) {
            $firstLine = 0;
        }

        return array_slice($file, $firstLine, $linesAround, true);

        /*if (!$filePath || !$line) {
            return null;
        }

        try {
            $file = new \SplFileObject($filePath);
            $file->setMaxLineLen(250);
            $file->seek(PHP_INT_MAX);

            $codeLines = [];

            $from = max(0, $line - $linesAround);
            $to = min($line + $linesAround, $file->key() + 1);

            $file->seek($from);

            while ($file->key() < $to && !$file->eof()) {
                $file->next();
                $codeLines[$file->key() + 1] = str_replace("\n", '', $file->current());
            }

            return $codeLines;
        } catch (\Exception $e) {
            return null;
        }*/
    }
}
