<?php

namespace CloudMonitor\Toolkit\Database\Providers;

use CloudMonitor\Toolkit\Core\CloudMonitor;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;

class QueryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['events']->listen(QueryExecuted::class, function (QueryExecuted $query) {
            if ($this->app['cloudmonitor']->isRecording() && $this->app['cloudmonitor']->segments() < CloudMonitor::SEGMENT_LIMIT) {
                $this->app['cloudmonitor']
                    ->startSegment(
                        $query->connectionName,
                        substr($query->sql, 0, 50)
                    )
                    ->start(microtime(true) - $query->time / 1000)
                    ->addContext(
                        'db',
                        [
                            'connection' => $query->connectionName,
                            'sql' => $query->sql,
                        ]
                    )->end($query->time);
            }
        });
    }
}
