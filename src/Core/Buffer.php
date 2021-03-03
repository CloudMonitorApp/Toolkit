<?php

namespace CloudMonitor\Toolkit\Core;

use Carbon\Carbon;

class Buffer
{
    private static $instance;
    private $stack;
    private $time;

    private function __construct() {
        $this->time = now();
    }

    public static function get(): Buffer
    {
        if (! self::$instance) {
            self::$instance = new Buffer;
        }

        return self::$instance;
    }

    public function add(Transportable $item)
    {
        if (count($this->stack) > 1000 && $item->model === 'segment') {
            return;
        }

        $this->stack[] = $item;
    }

    public function pluckAll(): array
    {
        $stack = $this->stack;
        $this->stack = [];
        
        return $stack;
    }

    public function lastCleared(): Carbon
    {
        return $this->time;
    }
}