<?php

namespace CloudMonitor\Toolkit\Console\Commands;

use CloudMonitor\Toolkit\Core\CloudMonitor;
use CloudMonitor\Toolkit\Core\Transport;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class Commands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudmonitor:commands';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get list of commands to listen for.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Transport::get('cmd', function($response) {
            Cache::put('cloudmonitor.commands', $response, 60*60*24);
        });
    }
}
