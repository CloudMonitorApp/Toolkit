<?php

namespace EmilMoe\CloudMonitor;

use Error;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class Issue
{
    /**
     * Dispatch issue to CloudMonitor.
     * 
     * @param  Throwable  $exception
     * @param  string     $language
     * @param  array      $error
     */
    public static function dispatch(Throwable $exception, string $language, array $error = null): void
    {
        if (self::isIgnored($exception)) {
            return;
        }
 
        Webhook::send('issue',
            [
                'app'   => self::issue($exception, $language, $error),
                'tags'  => self::tags(),
                'event' => self::event(),
                'trace' => self::trace($exception),
                'query' => DB::getQueryLog(),
            ]
        );
    }

    /**
     * Details about issue and the application.
     * 
     * @param  Throwable  $exception
     * @param  string     $language
     * @param  Error      $error
     * @return array
     */
    public static function issue(Throwable $exception, string $language, Error $error = null): array
    {
        return [
            'type'     => $language,
            'message'  => method_exists($exception, 'getMessage') ? $exception->getMessage() ?? '' : '',
            'line'     => method_exists($exception, 'getLine') ? $exception->getLine() ?? '' : '',
            'file'     => method_exists($exception, 'getFile') ? str_ireplace(base_path(), '', $exception->getFile()) ?? '' : '',
            'severity' => method_exists($exception, 'getSeverity') ? $exception->getSeverity() : '',
            'level'    => isset($error['level_name']) ? $error['level_name'] : '',
            'code'     => method_exists($exception, 'getCode') ? $exception->getCode() ?? '' : '',
            'class'    => get_class($exception) ?? '',
            'method'   => method_exists('Request', 'method') ? Request::method() ?? '' : '',
            'previous' => $exception->getPrevious() ?? '',
            'preview'  => method_exists($exception, 'getFile') && method_exists($exception, 'getLine')
                            ? self::makePreview($exception->getFile(), $exception->getLine()) : '',
            'url'      => app()->runningInConsole() ? 'Console' : url()->full(),
            'stage'    => env('APP_ENV', 'unknown stage'),
        ];
    }

    /**
     * Various tags that might be useful for debugging.
     * 
     * @return array
     */
    public static function tags(): array
    {
        return [
            'php_version'     => phpversion(),
            'os_name'         => php_uname('s'),
            'os_version'      => php_uname('r'),
            'host_name'       => php_uname('n'),
            'os_architecture' => php_uname('m'),
            'app_env'         => env('APP_ENV', null),
        ];
    }

    /**
     * Event related details.
     * 
     * @return array
     */
    public static function event(): array
    {
        return [
            'ip'         => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'user_id'    => auth()->check() ? auth()->id() : null,
            'user'       => auth()->check() ? auth()->user()->toJson() : null,
            'session'    => $_SESSION ?? null,
            'request'    => json_encode(Request::except(['password', 'password_repeat', 'password_again'])),
        ];
    }

    /**
     * Stack trace.
     * 
     * @param  Throwable  $exception
     * @return array
     */
    public static function trace(Throwable $exception): array
    {
        return collect($exception->getTrace())->map(function ($trace, $index) {
            return [
                'stack_key' => $index,
                'file'      => str_ireplace(base_path(), '', $trace['file'] ?? null) ?? null,
                'line'      => $trace['line'] ?? null,
                'function'  => $trace['function'] ?? null,
                'class'     => $trace['class'] ?? null,
                'type'      => $trace['type'] ?? null,
                'args'      => $trace['args'] ?? null,
                'preview'   => isset($trace['file'], $trace['line']) ? self::makePreview($trace['file'], $trace['line']) : null,
            ];
        })->toArray();
    }

    /**
     * Gather preview of source error.
     *
     * @param  string  $file
     * @param  int     $line
     * @return array
     */
    private static function makePreview(string $file, int $line): array
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

    /**
     * Is the exception set to be ignored.
     * 
     * @param  Throwable  $exception
     * @return bool
     */
    private static function isIgnored(Throwable $exception): bool
    {
        return collect(config('cloudmonitor.exceptions.ignore'))->contains(function(string $class) use($exception) {
            return $exception instanceof $class;
        });
    }
}