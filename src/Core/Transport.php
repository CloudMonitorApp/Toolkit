<?php

namespace CloudMonitor\Toolkit\Core;

use Closure;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Encryption\Encrypter;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class Transport
{
    public static function post($data = null, $prefix = ''): void
    {
        self::send('POST', $data, '', null, $prefix);
    }

    public static function get(string $arg, Closure $closure): void
    {
        self::send('GET', null, $arg, $closure);
    }

    private static function send(string $method, $data = null, string $arg = '', Closure $closure = null, $prefix = ''): void
    {
        file_put_contents(storage_path('cloudmonitor/'. $prefix .'-'. (is_array($data) ? $data['uuid'] : $data->uuid) .'.log'), json_encode($data, JSON_PRETTY_PRINT), FILE_APPEND);

        return;

        if (env('CLOUDMONITOR_KEY', null) === null || env('CLOUDMONITOR_SECRET', null) === null) {
            return;
        }

        $client = new Client(['verify' => env('CLOUDMONITOR_SSL_VERIFY', true)]);
        $response = null;

        $timestamp = time();

        try {
            $response = $client->request(
                $method,
                env('CLOUDMONITOR_URL', 'https://api.cloudmonitor.dk/') .'?arg='. $arg,
                [
                    'headers' => [
                        'X-CloudMonitor-Timestamp' => $timestamp,
                        'X-CloudMonitor-Version' => CloudMonitor::VERSION,
                        'X-CloudMonitor-Token' => env('CLOUDMONITOR_KEY'),
                        'X-CloudMonitor-Signature' => self::makeSignature($timestamp),
                    ],
                    'form_params' => [
                        'data' => self::encrypt(json_encode($data))
                    ]
                ]
            );

            if (isset($closure) && is_callable($closure)) {
                call_user_func_array($closure, [json_decode($response->getBody(), true)]);
            }
        } catch(ServerException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
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