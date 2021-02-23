<?php

namespace CloudMonitor\Toolkit\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Verify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudmonitor:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify CloudMonitor installation';

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
        User::all();
        DB::statement('select * from applications');
        
        $this->line('Verifiying installation');

        //Ping::send();
    }
}
