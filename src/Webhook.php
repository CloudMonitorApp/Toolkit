<?php

namespace EmilMoe\CloudMonitor;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use Illuminate\Encryption\Encrypter;
use GuzzleHttp\Psr7\Response;

class Webhook
{
    /**
     * Base URL without event relatede endpoint.
     * 
     * @var string
     */
    const BASE_URL = 'https://api.cloudmonitor.dk/hooks/';

    /**
     * Version string.
     * 
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Send event to CloudMonitor.
     * 
     * @param string $endpoint
     * @param array $data
     * @throws Exception
     */
    public static function send(string $endpoint, array $data): Response
    {
        $client = new Client();
        $response = null;

        $timestamp = time();

        try {
            $response = $client->request(
                'POST',
                self::BASE_URL . $endpoint,
                [
                    'headers' => [
                        'timestamp' => $timestamp,
                        'version' => self::VERSION,
                        'token' => env('CLOUDMONITOR_KEY'),
                        'signature' => self::makeSignature($timestamp),
                    ],
                    'form_params' => [
                        'installation' => env('CLOUDMONITOR_INSTALLATION', null),
                        'data' => self::encrypt(json_encode($data))
                    ]
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw new WebHookFailedException('Webhook received a non 200 response');
            }

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            dd($exception);
        }

        return $response;
    }

    /**
     * Encrypt message before sending.
     * 
     * @param string $data
     * @return string
     */
    public static function encrypt(string $data): string
    {
        $encrypter = new Encrypter(base64_decode(env('CLOUDMONITOR_SECRET')), 'AES-128-CBC');
        
        return $encrypter->encrypt($data);
    }

    /**
     * Generate signature for request.
     * 
     * @param int $timestamp
     * @return string
     */
    private static function makeSignature(int $timestamp): string
    {
        return hash_hmac(
            'sha256',
            env('CLOUDMONITOR_KEY') . $timestamp,
            env('CLOUDMONITOR_SECRET')
        );
    }
}