<?php

namespace CloudMonitor\Toolkit\Core;

use CloudMonitor\Toolkit\Core\Meta\Client;
use CloudMonitor\Toolkit\Core\Meta\Host;
use CloudMonitor\Toolkit\Core\Meta\Request;
use CloudMonitor\Toolkit\Core\Meta\Session;
use CloudMonitor\Toolkit\Core\Meta\User;

class Segment implements Transportable
{
    public $model;
    public $type;
    public $label;
    public $transaction;
    public $start;
    public $timestamp;
    public $duration;
    public $context;
    public $uuid;
    public $meta;
    private $skip;

    /**
     * Construct new Segment.
     * 
     * @param Transaction $transaction
     * @param string|null $label
     */
    public function __construct(Transaction $transaction, $label = null)
    {
        $this->model = 'segment';
        $this->uuid = $transaction->uuid;
        $this->type = $transaction->type;
        $this->label = $label;
        $this->meta = [
            'host' => new Host,
            'user' => new User,
            'request' => new Request,
            'client' =>  new Client,
            'session' => new Session,
        ];
        $this->transaction = collect($transaction)->only(['hash', 'timestamp'])->toArray();
    }

    /**
     * Register start time of segment.
     * 
     * @param int $time = null
     * @return Segment
     */
    public function start($time = null): Segment
    {
        $initial = is_null($time) ? microtime(true) : $time;

        $this->start = round(($initial - $this->transaction['timestamp'])*1000, 2);
        $this->timestamp = is_null($time) ? microtime(true) : $time;
        
        return $this;
    }

    /**
     * Register end time of segment.
     * 
     * @param int $duration = null
     * @return Segment
     */
    public function end($duration = null): Segment
    {
        $this->duration = $duration ?? round((microtime(true) - $this->timestamp)*1000, 2);
        //dispatch(new Queue($this));

        return $this;
    }

    /**
     * Add context to segment.
     * 
     * @param string $label
     * @param mixed $data
     * @return Segment
     */
    public function addContext($label, $data): Segment
    {
        $this->context[$label] = $data;

        return $this;
    }

    /**
     * Set context.
     * 
     * @param array $context
     * @return Segment
     */
    public function setContext(array $context): Segment
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context.
     * 
     * @param string|null $label = null
     * @return mixed
     */
    public function getContext(?string $label = null)
    {
        if (is_string($label)) {
            return $this->context[$label];
        }

        return $this->context;
    }
}