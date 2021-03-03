<?php

namespace CloudMonitor\Toolkit\Core;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Encryption\Encrypter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Queue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The transport instance.
     *
     * @var Transportable
     */
    protected $transport;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transportable $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Execute the job.
     *
     * @param  Transportable  $transport
     * @return void
     */
    public function handle()
    {
        //file_put_contents(dirname(__DIR__, 1) .'/debug/queue.json', json_encode($this->transport, JSON_PRETTY_PRINT), FILE_APPEND);

        Transport::post([$this->transport]);

        //Buffer::get()->add($this->transport);
    }
}