<?php

namespace EmilMoe\CloudMonitor\Channels;

use GuzzleHttp\Client;
use Illuminate\Log\Logger;
use EmilMoe\CloudMonitor\Webhook;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Spatie\Backup\Notifications\BaseNotification;
use EmilMoe\CloudMonitor\Exceptions\WebHookFailedException;

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
        Webhook::send(
            'backup',
            [
                'event' => $event,
                'code' => $code,
            ]
        );
    }
}
