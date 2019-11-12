<?php

namespace EmilMoe\CloudMonitor;

use Illuminate\Support\ServiceProvider;
use EmilMoe\CloudMonitor\Exceptions\Handler;
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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}