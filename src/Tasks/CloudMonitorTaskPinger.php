<?php

namespace EmilMoe\CloudMonitor\Tasks;

use EmilMoe\CloudMonitor\Webhook;

class CloudMonitorTaskPing
{
    /**
     * 
     */
    public static function begin(string $id): string
    {

    }

    /**
     *
     */
    public static function success(string $id): string
    {
        Webhook::send(json_encode([
            'id' => $id,
            'success' => true
        ]));
    }

    /**
     *
     */
    public static function failure(string $id): string
    {
        Webhook::send(json_encode([
            'id' => $id,
            'success' => false
        ]));
    }
}