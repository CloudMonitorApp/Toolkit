<?php

namespace EmilMoe\CloudMonitor\Channels;

use EmilMoe\CloudMonitorToolkit\Exceptions\WebHookFailedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Log\Logger;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Spatie\Backup\Notifications\BaseNotification;

class CloudMonitorChannel extends BaseNotification
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param Notifiable $notifiable
     * @param Notification $notification
     * @param string $event
     * @param int $code
     * @param string $message
     * @throws WebHookFailedException
     */
    public function dispatch($notifiable, Notification $notification, string $event, int $code)
    {
        if (env('CLOUDMONITOR_KEY', null) === null || env('CLOUDMONITOR_SECRET', null) === null) {
            return null;
        }

        $timestamp = now()->timestamp;

        try {
            $response = $this->client->request(
                'POST',
                'https://api.cloudmonitor.dk/backup',
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
                        'event' => $event,
                        'code' => $code,
                    ]
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw new WebHookFailedException('Webhook received a non 200 response');
            }
            
            return;

        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() !== 410) {
                throw new WebHookFailedException($exception->getMessage(), $exception->getCode(), $exception);
            }
        } catch (GuzzleException $exception) {
            throw new WebHookFailedException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->logger->error('CloudMonitor failed in posting to '. env('CLOUDMONITOR_URL'));
    }
}
