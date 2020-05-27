<?php

namespace CloudMonitor\Toolkit\Console\Commands;

use Illuminate\Console\Command;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudmonitor:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install CloudMonitor';

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
