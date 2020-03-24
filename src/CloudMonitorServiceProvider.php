<?php

namespace EmilMoe\CloudMonitor;

use Illuminate\Support\ServiceProvider;
use EmilMoe\CloudMonitor\Exceptions\Handler;
use EmilMoe\CloudMonitor\Console\Commands\TestException;
use EmilMoe\CloudMonitor\Console\Commands\TestError;
use Illuminate\Contracts\Debug\ExceptionHandler;

class CloudMonitorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
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
            ]);
        }
    }
}