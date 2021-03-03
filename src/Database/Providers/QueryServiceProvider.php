<?php

namespace CloudMonitor\Toolkit\Database\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;

class QueryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['events']->listen(QueryExecuted::class, function (QueryExecuted $query) {
            if ($this->app['cloudmonitor']->isRecording()) {
                $segment = $this->app['cloudmonitor']
                    ->startSegment(
                        $query->connectionName,
                        substr($query->sql, 0, 50)
                    );
                    
                if (! $segment) {
                    return;
                }

                $segment->start(
                        microtime(true) - $query->time/ 1000
                    );
                
                $context = [
                    'connection' => $query->connectionName,
                    'sql' => $query->sql,
                ];
                
                $segment->addContext(
                    'db',
                    $context
                )->end($query->time);
            }
        });
    }
}
