<?php

namespace CloudMonitor\Toolkit;

class Ping
{
    /**
     * Ping application to tell the connection is alive.
     * 
     * @return void
     */
    public static function send(): void
    {
        Webhook::send('ping');
    }
}