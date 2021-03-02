<?php

namespace CloudMonitor\Toolkit\Core;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Encryption\Encrypter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Queue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The transport instance.
     *
     * @var Transportable
     */
    protected $transport;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transportable $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Execute the job.
     *
     * @param  Transportable  $transport
     * @return void
     */
    public function handle()
    {
        //file_put_contents(dirname(__DIR__, 1) .'/debug/queue.json', json_encode($this->transport, JSON_PRETTY_PRINT), FILE_APPEND);

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
                        'data' => self::encrypt(json_encode($this->transport))
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