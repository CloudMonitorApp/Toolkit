<?php

namespace EmilMoe\CloudMonitor;

use Error;
use Illuminate\Support\Facades\Request;
use Throwable;

trait Report
{
    public function dispatch(string $endpoint, array $data): void
    {

    }

    private static function getApp(Throwable $exception, string $language, Error $error = null): array
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
            'method'   => method_exists(static::Request, 'method') ? Request::method() ?? '' : '',
            'previous' => $exception->getPrevious() ?? '',
            'preview'  => method_exists($exception, 'getFile') && method_exists($exception, 'getLine')
                            ? self::getPreview($exception->getFile(), $exception->getLine()) : '',
            'url'      => app()->runningInConsole() ? 'Console' : url()->full(),
            'stage'    => env('APP_ENV', 'unknown stage'),
        ];
    } 

    private function getEvent()
    {

    }

    private function getTrace()
    {

    }

    private function getPreview()
    {
        
    }
}