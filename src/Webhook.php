<?php

namespace EmilMoe\CloudMonitor;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use Illuminate\Encryption\Encrypter;

class Webhook
{
    /**
     * @param Exception $e
     * @throws Exception
     */
    public static function send(string $endpoint, string $data): void
    {
        $client = new Client();

        $timestamp = now()->timestamp;

        try {
            $response = $client->request(
                'POST',
                'https://cloudmonitor.dk/api/hooks/'. $endpoint,
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
                        'data' => self::encrypt($data)
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

    /**
     * Encrypt message before sending.
     */
    public static function encrypt(string $data): string
    {
        $encrypter = new Encrypter(base64_decode(env('CLOUDMONITOR_SECRET')), 'AES-128-CBC');
        return $encrypter->encrypt($data);
    }
}