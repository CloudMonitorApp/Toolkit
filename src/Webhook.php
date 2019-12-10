<?php

namespace EmilMoe\CloudMonitor;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class Webhook
{
    /**
     * @param Exception $e
     * @throws Exception
     */
    public static function send(string $data): void
    {
        $client = new Client();

        $timestamp = now()->timestamp;

        try {
            $response = $client->request(
                'POST',
                'https://cloudmonitor.dk/api/error',
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
                        'installation' => env('CLOUDMONITOR_INSTALLATION', null),
                        'data' => $data
                    ]
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw new WebHookFailedException('Webhook received a non 200 response');
            }

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}