<?php

namespace CloudMonitor\Toolkit\Core;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Encryption\Encrypter;

class Transport
{
    /**
     * Queue.
     * 
     * @var array
     */
    private $queue = [];

    /**
     * Add entry to queue.
     * 
     * @param $item
     * @return Transport
     */
    public function addEntry($item): Transport
    {
        $this->queue[] = $item;
        
        return $this;
    }

    /**
     * Flush queue.
     * 
     * @return void
     */
    public function flush(): void
    {
        if (empty($this->queue)) {
            return;
        }

        /*if (! in_array($this->queue[0]->name, CloudMonitor::FILTERED)) {
            file_put_contents(dirname(__DIR__, 1) .'/debug/final-'. time() .'.json', json_encode($this->queue, JSON_PRETTY_PRINT));
        }*/

        $this->queue = [];
    }

    public static function send($item)
    {
        // file_put_contents(dirname(__DIR__, 1) .'/debug/queue.json', json_encode($item, JSON_PRETTY_PRINT), FILE_APPEND);

        if (env('CLOUDMONITOR_KEY', null) === null || env('CLOUDMONITOR_SECRET', null) === null) {
            return null;
        }

        $client = new Client(['verify' => env('CLOUDMONITOR_SSL_VERIFY', true)]);
        $response = null;

        $timestamp = time();

        try {
            $response = $client->request(
                'POST',
                env('CLOUDMONITOR_URL', 'https://api.cloudmonitor.dk/'),
                [
                    'headers' => [
                        'X-CloudMonitor-Timestamp' => $timestamp,
                        'X-CloudMonitor-Version' => CloudMonitor::VERSION,
                        'X-CloudMonitor-Token' => env('CLOUDMONITOR_KEY'),
                        'X-CloudMonitor-Signature' => self::makeSignature($timestamp),
                    ],
                    'form_params' => [
                        'data' => self::encrypt(json_encode($item))
                    ]
                ]
            );
        } catch(ServerException $e) {
            return $response;
        } catch (Exception $e) {
            // Proceed
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