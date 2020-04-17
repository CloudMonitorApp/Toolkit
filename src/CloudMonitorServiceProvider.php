<?php

namespace EmilMoe\CloudMonitor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use EmilMoe\CloudMonitor\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use EmilMoe\CloudMonitor\Console\Commands\TestTask;
use EmilMoe\CloudMonitor\Console\Commands\TestError;
use EmilMoe\CloudMonitor\Console\Commands\TestBackup;
use EmilMoe\CloudMonitor\Console\Commands\TestException;
use EmilMoe\CloudMonitor\Listeners\Backup\BackupHasFailed;
use EmilMoe\CloudMonitor\Listeners\Backup\CleanupHasFailed;
use EmilMoe\CloudMonitor\Listeners\Backup\BackupWasSuccessful;
use EmilMoe\CloudMonitor\Listeners\Backup\CleanupWasSuccessful;
use EmilMoe\CloudMonitor\Listeners\Backup\HealthyBackupWasFound;
use EmilMoe\CloudMonitor\Listeners\Backup\UnhealthyBackupWasFound;

class CloudMonitorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        DB::connection()->enableQueryLog();
        
        $this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        $this->mergeConfigFrom(
            __DIR__.'/config.php', 'cloudmonitor'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ .'/config.php' => config_path('cloudmonitor.php'),
        ]);

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestException::class,
                TestError::class,
                TestTask::class,
                TestBackup::class,
            ]);

            if (config('cloudmonitor.backup.listen')) {
                if (class_exists(\Spatie\Backup\Events\BackupWasSuccessful::class)) {
                    Event::listen(\Spatie\Backup\Events\BackupWasSuccessful::class, BackupWasSuccessful::class);
                }

                if (class_exists(\Spatie\Backup\Events\BackupHasFailed::class)) {
                    Event::listen(\Spatie\Backup\Events\BackupHasFailed::class, BackupHasFailed::class);
                }

                if (class_exists(\Spatie\Backup\Events\CleanupWasSuccessful::class)) {
                    Event::listen(\Spatie\Backup\Events\CleanupWasSuccessful::class, CleanupWasSuccessful::class);
                }

                if (class_exists(\Spatie\Backup\Events\CleanupHasFailed::class)) {
                    Event::listen(\Spatie\Backup\Events\CleanupHasFailed::class, CleanupHasFailed::class);
                }

                if (class_exists(\Spatie\Backup\Events\HealthyBackupWasFound::class)) {
                    Event::listen(\Spatie\Backup\Events\HealthyBackupWasFound::class, HealthyBackupWasFound::class);
                }

                if (class_exists(\Spatie\Backup\Events\UnhealthyBackupWasFound::class)) {
                    Event::listen(\Spatie\Backup\Events\UnhealthyBackupWasFound::class, UnhealthyBackupWasFound::class);
                }
            }
        }
    }
}