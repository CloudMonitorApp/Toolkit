<?php

namespace CloudMonitor\Toolkit;

use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class Issue
{
    /**
     * Dispatch issue to CloudMonitor.
     * 
     * @param  IssueContract  $issue
     * @param  string         $language
     * @param  array          $error
     */
    public static function dispatch(IssueContract $issue, string $language, array $error = []): void
    {
        if (self::isIgnored($issue)) {
            return;
        }

        Webhook::send('issue',
            [
                'app'     => self::issue($issue, $language, $error),
                'tags'    => self::tags(),
                'event'   => self::event(),
                'trace'   => $issue->getTrace(),
                'queries' => DB::getQueryLog(),
            ]
        );
    }

    /**
     * Gather preview of source error.
     *
     * @param  string  $file
     * @param  int     $line
     * @return array
     */
    public static function makePreview(string $file, int $line): array
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
     * Is the issue set to be ignored.
     * 
     * @param  IssueContract  $issue
     * @return bool
     */
    private static function isIgnored(IssueContract $issue): bool
    {
        return collect(config('cloudmonitor.exceptions.ignore'))->contains(function(string $class) use($issue) {
            return $issue instanceof $class;
        });
    }

    /**
     * Details about issue and the application.
     * 
     * @param  IssueContract  $exception
     * @param  string         $language
     * @param  array          $error
     * @return array
     */
    public static function issue(IssueContract $issue, string $language, array $error = []): array
    {
        return [
            'type' => $language,
            'message' => $issue->getMessage(),
            'line' => $issue->getLine(),
            'file' => $issue->getFile(),
            'severity' => $issue->getSeverity(),
            'level' => 0,
            'code' => $issue->getCode(),
            'class' => $issue->getClass(),
            'method' => $issue->getMethod(),
            'previous' => $issue->getPrevious(),
            'preview' => $issue->getPreview(),
            'url' => app()->runningInConsole() ? 'Console' : $issue->getUrl(),
            'stage' => env('APP_ENV', 'unknown stage'),
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
            'ip'         => $_SERVER[config('cloudmonitor.ip-source', 'REMOTE_ADDR')] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => Request::header('User-Agent'),
            'user_id'    => auth()->check() ? auth()->id() : null,
            'user'       => auth()->check() ? auth()->user()->toJson() : null,
            'session'    => $_SESSION ?? null,
            'request'    => json_encode(Request::except(['password', 'password_repeat', 'password_again'])),
        ];
    }
}