<?php

namespace EmilMoe\CloudMonitor\Console\Commands;

use EmilMoe\CloudMonitor\Tasks\Ping;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

class TestTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudmonitor:task {task : Task with parameters}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test execution of task and report it to CloudMonitor';

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
        $schedule = new Schedule();

        app()->booted(function() use(&$schedule) {
            $schedule = app()->make(Schedule::class);
            $schedule->command($this->argument('task'))->daily();
        });
        
        $file = Ping::before($this->argument('task'), $schedule)();
        file_put_contents($file, 'Test  run');
        Ping::after($this->argument('task'), $schedule)();

        $this->line('Done');
    }
}
