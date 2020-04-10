<?php

namespace EmilMoe\CloudMonitor\Console\Commands;

use Illuminate\Console\Command;

class TestBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudmonitor:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test error to CloudMonitor';

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
        // nothing
    }
}
