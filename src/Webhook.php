<?php

namespace EmilMoe\CloudMonitor;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Encryption\Encrypter;
use GuzzleHttp\Exception\ClientException;

class Webhook
{
    /**
     * Version string.
     * 
     * @var string
     */
    const VERSION = '1.0.2';

    /**
     * Base URL without event relatede endpoint.
     * 
     * @var string
     */
    private static $baseUrl;

    /**
     * Send event to CloudMonitor.
     * 
     * @param string $endpoint
     * @param array $data
     * @throws Exception
     */
    public static function send(string $endpoint, array $data): ?Response
    {
        self::$baseUrl = env('CLOUDMONITOR_URL', 'https://api.cloudmonitor.dk/');

        if (env('CLOUDMONITOR_KEY', null) === null || env('CLOUDMONITOR_SECRET', null) === null) {
            return null;
        }

        $client = new Client();
        $response = null;

        $timestamp = time();

        try {
            $response = $client->request(
                'POST',
                self::$baseUrl . $endpoint,
                [
                    'headers' => [
                        'timestamp' => $timestamp,
                        'version' => self::VERSION,
                        'token' => env('CLOUDMONITOR_KEY'),
                        'signature' => self::makeSignature($timestamp),
                        'installation' => env('CLOUDMONITOR_INSTALLATION', null),
                    ],
                    'form_params' => [
                        'data' => self::encrypt(json_encode($data))
                    ]
                ]
            );

            if ($response->hasHeader('x-success')) {
                print('Job processed with success: '. self::$baseUrl . $endpoint . PHP_EOL);
            }
        } catch (ClientException $e) {
            if($e->getResponse()->getStatusCode() === 400) {
                dd($e->getResponse()->getHeaders()['x-error'][0]);
            }

            dd($e);
        } catch (Exception $e) {
            dd($e);
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