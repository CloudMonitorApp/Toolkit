<?php

namespace EmilMoe\CloudMonitor\Channels;

use GuzzleHttp\Client;
use Illuminate\Log\Logger;
use EmilMoe\CloudMonitor\Webhook;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
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
    
    /**
     * New instance.
     * 
     * @param  Client $client
     * @param  Logger $logger
     */
    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * Dispatch backup event to CloudMonitor.
     * 
     * @param Notifiable $notifiable
     * @param Notification $notification
     * @param string $event
     * @param int $code
     * @param string $message
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
