<?php

namespace CloudMonitor\Toolkit\Core;

use Closure;
use Exception;
use Throwable;
use InvalidArgumentException;
use CloudMonitor\Toolkit\Core\Segment;
use CloudMonitor\Toolkit\Core\Transport;
use CloudMonitor\Toolkit\Core\Transaction;
use CloudMonitor\Toolkit\Error\Models\Error;

class CloudMonitor
{
    /**
     * Commands that must be ignored.
     * 
     * @var array
     */
    const FILTERED = [null, 'Standard input code'];

    const VERSION = '1.1.0';

    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * @var Transport
     */
    private $transport;

    /**
     * Constructs new CloudMonitor instance.
     */
    public function __construct()
    {
        $this->transport = new Transport();
        register_shutdown_function([$this, 'flush']);
    }

    /**
     * Creates new transaction and starts it.
     * 
     * @param $transaction
     * @param string $type Type of transaction.
     */
    public function startTransaction($transaction, string $type): Transaction
    {
        $this->transaction = new Transaction(addslashes($transaction), $type);
        $this->transaction->start();
        $this->addEntries($this->transaction);

        return $this->transaction;
    }

    /**
     * Is a transaction currently being monitored.
     * 
     * @return bool
     */
    public function isRecording(): bool
    {
        return isset($this->transaction);
    }

    /**
     * Retrieve current monitored transaction.
     * 
     * @return Transaction
     */
    public function currentTransaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * Add entries to to transport.
     * 
     * @param mixed $entries
     * @return CloudMonitor
     */
    public function addEntries($entries): CloudMonitor
    {
        $entries = is_array($entries) ? $entries : [$entries];

        foreach ($entries as $entry) {
            $this->transport->addEntry($entry);
        }

        return $this;
    }

    /**
     * Flush the transaction.
     * 
     * @return void
     */
    public function flush(): void
    {
        if (!$this->isRecording()) {
            return;
        }

        if (!$this->transaction->isEnded()) {
            $this->transaction->end();
        }

        $this->transport->flush();
        unset($this->transaction);
    }

    /**
     * Start a new segment.
     * 
     * @param $type
     * @param $label
     * @return Segment
     */
    public function startSegment($type, $label = null): Segment
    {
        $segment = new Segment($this->transaction, addslashes($type), $label);
        $segment->start();
        $this->addEntries($segment);

        return $segment;
    }

    /**
     * Add segment.
     * 
     * @param $callback
     * @param type
     * @param label
     * @param throw
     * @throws Throwable
     * @return Closure
     */
    /*public function addSegment(Closure $callback, $type, $label = null, $throw = false): Closure
    {
        $segment = $this->startSegment($type, $label);

        try {
            return $callback($segment);
        } catch (Throwable $exception) {
            if ($throw === true) {
                throw $exception;
            }

            $this->reportException($exception);
        } finally {
            $segment->end();
        }
    }*/

    /**
     * Exception reporter.
     * 
     * @param Throwable $exception
     * @param bool handled
     * @return Error
     */
    public function reportException(Throwable $exception, $handled = true): Error
    {
        if (!$exception instanceof Exception && !$exception instanceof Throwable) {
            throw new InvalidArgumentException('$exception need to be an instance of Exception or Throwable.');
        }

        if (!$this->isRecording()) {
            $this->startTransaction($exception->getMessage(), Transaction::TRANSACTION_EXCEPTION);
        }

        $segment = $this->startSegment('exception', substr($exception->getMessage(), 0, 50));

        $error = (new Error($exception, $this->transaction))->setHandled($handled);

        $this->addEntries($error);

        $segment->addContext('Error', $error)->end();

        return $error;
    }
}