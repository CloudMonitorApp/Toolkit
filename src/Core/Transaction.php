<?php

namespace CloudMonitor\Toolkit\Core;

use CloudMonitor\Toolkit\Core\Meta\Host;
use CloudMonitor\Toolkit\Core\Meta\User;
use CloudMonitor\Toolkit\Core\Meta\Client;
use CloudMonitor\Toolkit\Core\Meta\Request;
use CloudMonitor\Toolkit\Core\Meta\Session;

class Transaction
{
    /**
     * Types of valid transactions.
     * 
     * @var string
     */
    const TRANSACTION_COMMAND = 'command';
    const TRANSACTION_EXCEPTION = 'exception';
    const TRANSACTION_REQUEST = 'request';
    const TRANSACTION_BACKUP = 'backup';

    public $name;
    public $timestamp;
    public $duration;
    public $uuid;
    public $type;
    public $context;
    public $result;
    public $meta;

    /**
     * Construct new transaction.
     * 
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
        $this->uuid = uuid_create(UUID_TYPE_RANDOM);
        $this->meta = [
            'user' => new User,
            'request' => new Request,
            'session' => new Session,
            'client' => new Client,
            'host' => new Host,
        ];
    }
    
    /**
     * Register start time of transaction.
     * 
     * @param int|null $time = null
     * @return Transaction
     */
    public function start($time = null): Transaction
    {
        $this->timestamp = is_null($time) ? microtime(true) : $time;
        Transport::send($this);

        return $this;
    }

    /**
     * Register end time of transaction.
     * 
     * @param int|null $duration = null
     * @return Transaction
     */
    public function end($duration = null)
    {
        $this->duration = $duration ?? round((microtime(true) - $this->timestamp)*1000, 2);
        Transport::send($this);
        
        return $this;
    }

    /**
     * Is the transaction ended.
     * 
     * @return bool
     */
    public function isEnded(): bool
    {
        return isset($this->duration) && $this->duration > 0;
    }

    /**
     * Add context to transaction.
     * 
     * @param string $label
     * @param mixed $data
     * @return Transaction
     */
    public function addContext(string $label, $data): Transaction
    {
        $this->context[$label] = $data;

        return $this;
    }

    /**
     * Set result of transaction.
     * 
     * @param string $result
     * @return Transaction
     */
    public function setResult(string $result): Transaction
    {
        $this->result = $result;

        return $this;
    }
}