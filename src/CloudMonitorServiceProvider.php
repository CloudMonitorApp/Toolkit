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
use EmilMoe\CloudMonitor\Listeners\Backup\BackupWasSuccessful;
use EmilMoe\CloudMonitor\Listeners\Backup\CleanupHasFailed;
use EmilMoe\CloudMonitor\Listeners\Backup\CleanupWasSuccessful;
use EmilMoe\CloudMonitor\Listeners\Backup\HealthyBackupWasFound;
use Spatie\Backup\Events\BackupHasFailed as SpatieBackupHasFailed;
use Spatie\Backup\Events\CleanupHasFailed as SpatieCleanupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful as SpatieBackupWasSuccessful;
use Spatie\Backup\Events\CleanupWasSuccessful as SpatieCleanupWasSuccessful;
use Spatie\Backup\Events\HealthyBackupWasFound as SpatieHealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound as SpatieUnhealthyBackupWasFound;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound;

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
                if (class_exists(SpatieBackupWasSuccessful::class)) {
                    Event::listen(SpatieBackupWasSuccessful::class, BackupWasSuccessful::class);
                }

                if (class_exists(SpatieBackupHasFailed::class)) {
                    Event::listen(SpatieBackupHasFailed::class, BackupHasFailed::class);
                }

                if (class_exists(SpatieCleanupWasSuccessful::class)) {
                    Event::listen(SpatieCleanupWasSuccessful::class, CleanupWasSuccessful::class);
                }

                if (class_exists(SpatieCleanupHasFailed::class)) {
                    Event::listen(SpatieCleanupHasFailed::class, CleanupHasFailed::class);
                }

                if (class_exists(SpatieHealthyBackupWasFound::class)) {
                    Event::listen(SpatieHealthyBackupWasFound::class, HealthyBackupWasFound::class);
                }

                if (class_exists(SpatieUnhealthyBackupWasFound::class)) {
                    Event::listen(SpatieUnhealthyBackupWasFound::class, UnhealthyBackupWasFound::class);
                }
            }
        }
    }
}