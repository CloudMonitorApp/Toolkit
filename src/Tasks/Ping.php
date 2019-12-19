<?php

namespace EmilMoe\CloudMonitor\Tasks;

use EmilMoe\CloudMonitor\Webhook;

class Ping
{
    /**
     * 
     */
    public static function before(string $id): void
    {
        Webhook::send(json_encode([
            'id' => $id,
            'event' => 'begin',
        ]));
    }

    /**
     *
     */
    public static function success(string $id): void
    {
        Webhook::send(json_encode([
            'id' => $id,
            'event' => 'success',
        ]));
    }

    /**
     *
     */
    public static function failure(string $id): void
    {
        Webhook::send(json_encode([
            'id' => $id,
            'event' => 'failure',
        ]));
    }
}