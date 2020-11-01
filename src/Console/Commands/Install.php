<?php

namespace CloudMonitor\Toolkit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudmonitor:install {key} {secret}';

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
        if(strpos(file_get_contents(base_path('.env')), 'CLOUDMONITOR_KEY') !== true) {
            file_put_contents(base_path('.env'), PHP_EOL .'CLOUDMONITOR_KEY='. $this->argument('key'), FILE_APPEND);
        }
    
        if(strpos(file_get_contents(base_path('.env')), 'CLOUDMONITOR_SECRET') !== true) {
            file_put_contents(base_path('.env'), PHP_EOL .'CLOUDMONITOR_SECRET='. $this->argument('secret'), FILE_APPEND);
        }

        Artisan::call('cloudmonitor:verify');

        $this->line('Installed!');
    }
}
