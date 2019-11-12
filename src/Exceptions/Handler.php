<?php

namespace EmilMoe\CloudMonitor\Exceptions;

use App\Exceptions\Handler as ExceptionHandler;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class Handler extends ExceptionHandler
{
    private $client;

    /**
     * @param Exception $e
     * @throws Exception
     */
    public function report(Exception $e)
    {
        Log::info('Exception handler');

        parent::report($e);

        $this->client = new Client();

        $timestamp = now()->timestamp;

        try {
            $response = $this->client->request(
                'POST',
                'https://cloudmonitor.dk/api/hooks/error',
                [
                    'headers' => [
                        'timestamp' => $timestamp,
                        'token' => env('CLOUDMONITOR_KEY'),
                        'signature' => hash_hmac(
                            'sha256',
                            env('CLOUDMONITOR_KEY') . $timestamp,
                            env('CLOUDMONITOR_SECRET')
                        ),
                    ],
                    'form_params' => [
                        'event' => 'exception',
                        'data' => $this->getData($e)
                    ]
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw new WebHookFailedException('Webhook received a non 200 response');
            }

            Log::debug('Webhook successfully posted to '. env('CLOUDMONITOR_URL'));

            return;

        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() !== 410) {
                throw new WebHookFailedException($exception->getMessage(), $exception->getCode(), $exception);
            }
        } catch (GuzzleException $exception) {
            throw new WebHookFailedException($exception->getMessage(), $exception->getCode(), $exception);
        } finally {
            Log::info('CloudMonitor closed client');
        }

        Log::error('CloudMonitor failed in posting to '. env('CLOUDMONITOR_URL'));
    }

    private function getApp(Exception $e): array
    {
        return [
            'type' => 'php',
            'message' => $e->getMessage() ?? '',
            'line' => $e->getLine() ?? '',
            'file' => $e->getFile() ?? '',
            'severity' => $e instanceof \Exception ? 0 : $e->getSeverity() ?? '',
            'code' => $e->getCode() ?? '',
            'class' => get_class($e) ?? '',
            'original_class' => $e instanceof \Exception ? 'Exception' : $e->getOriginalClassName() ?? '',
            'method' => Request::method(),
            'previous' => $e->getPrevious() ?? '',
            'preview' => $this->getPreview($e->getFile(), $e->getLine()),
            'url' => url()->full(),
            'stage' => env('APP_ENV', 'unknown APP_ENV'),
        ];
    }

    private function getIncident(Exception $e): array
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
     * @return array
     */
    private function getData(Exception $e)
    {
        $encrypter = new Encrypter(env('CLOUDMONITOR_SECRET'), 'AES-128-CBC');

        return $encrypter->encrypt(
            json_encode(
                [
                    'app' => $this->getApp($e),
                    'incident' => $this->getIncident($e),
                    'trace' => $this->getTrace($e),
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