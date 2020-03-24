<?php

namespace EmilMoe\CloudMonitor\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class TestException extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudmonitor:exception';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test exception to CloudMonitor';

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
        throw new Exception('Test exception');
    }
}
