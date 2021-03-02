<?php

namespace CloudMonitor\Toolkit;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use CloudMonitor\Toolkit\Core\CloudMonitor;
use Illuminate\Console\Scheduling\Schedule;
use CloudMonitor\Toolkit\Console\Commands\Verify;
use CloudMonitor\Toolkit\Console\Commands\Install;
use CloudMonitor\Toolkit\Console\Commands\Commands;
use CloudMonitor\Toolkit\Console\Commands\TestError;
use CloudMonitor\Toolkit\Console\Commands\TestBackup;
use CloudMonitor\Toolkit\Console\Commands\TestException;
use CloudMonitor\Toolkit\Listeners\Backup\BackupHasFailed;
use CloudMonitor\Toolkit\Listeners\Backup\CleanupHasFailed;
use CloudMonitor\Toolkit\Auth\Providers\AuthServiceProvider;
use CloudMonitor\Toolkit\Email\Providers\EmailServiceProvider;
use CloudMonitor\Toolkit\Listeners\Backup\BackupWasSuccessful;
use CloudMonitor\Toolkit\Redis\Providers\RedisServiceProvider;
use CloudMonitor\Toolkit\Listeners\Backup\CleanupWasSuccessful;
use CloudMonitor\Toolkit\Listeners\Backup\HealthyBackupWasFound;
use CloudMonitor\Toolkit\Database\Providers\QueryServiceProvider;
use CloudMonitor\Toolkit\Command\Providers\CommandServiceProvider;
use CloudMonitor\Toolkit\Listeners\Backup\UnhealthyBackupWasFound;
use CloudMonitor\Toolkit\Error\Providers\ExceptionsServiceProvider;
use CloudMonitor\Toolkit\Notification\Providers\NotificationServiceProvider;
use CloudMonitor\Toolkit\ScheduledTask\Providers\ScheduledTaskServiceProvider;

class CloudMonitorServiceProvider extends ServiceProvider
{
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

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('cloudmonitor:commands')->daily();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestException::class,
                TestError::class,
                TestBackup::class,
                Install::class,
                Verify::class,
                Commands::class,
            ]);

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

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*$this->mergeConfigFrom(
            __DIR__.'/config.php', 'cloudmonitor'
        );*/

        $this->app->singleton('cloudmonitor', function () {
            return new CloudMonitor();
        });

        $this->registerServiceProviders();
    }

    private function registerServiceProviders(): void
    {
        if ($this->app->runningInConsole()) {
            $this->app->register(CommandServiceProvider::class);
        }

        if ($this->app->runningInConsole()) {
            $this->app->register(ScheduledTaskServiceProvider::class);
        }
        
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(ExceptionsServiceProvider::class);
        $this->app->register(QueryServiceProvider::class);
        $this->app->register(NotificationServiceProvider::class);
        $this->app->register(EmailServiceProvider::class);
        $this->app->register(RedisServiceProvider::class);
    }
}