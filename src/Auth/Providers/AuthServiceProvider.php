<?php

namespace CloudMonitor\Toolkit\Auth\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use CloudMonitor\Toolkit\Core\Facades\CloudMonitor;

class AuthServiceProvider extends ServiceProvider
{
    protected $segments = [];
    
    public function boot() 
    {
        Gate::before([$this, 'gateBefore']);
        Gate::after([$this, 'gateAfter']);
    }

    public function gateBefore(Authenticatable $user, $ability, $arguments)
    {
        if (CloudMonitor::isRecording()) {
            $this->segments[$this->generateUniqueKey(($this->formatArguments($arguments)))]
                = $this->app['cloudmonitor']->startSegment('gate', 'Authorization::'.$ability);
        }
    }

    public function gateAfter(Authenticatable $user, $ability, $result, $arguments)
    {
        $arguments = $this->formatArguments($arguments);
        $key = $this->generateUniqueKey($arguments);

        if (array_key_exists($key, $this->segments)) {
            $this->segments[$key]
                ->addContext('Check', [
                    'ability' => $ability,
                    'result' => $result ? 'allowed' : 'denied',
                    'arguments' => $arguments,
                ])
                ->end();

            if ($caller = $this->getCallerFromStackTrace()) {
                $this->segments[$key]
                    ->addContext('Caller', [
                        'file' => $caller['file'],
                        'line' => $caller['line'],
                    ]);
            }
        }

        return $result;
    }

    public function generateUniqueKey(array $data)
    {
        return md5(json_encode($data));
    }

    public function formatArguments(array $arguments)
    {
        return array_map(function ($item) {
            return $item instanceof Model ? $this->formatModel($item) : $item;
        }, $arguments);
    }

    public function formatModel($model)
    {
        return get_class($model).':'.$model->getKey();
    }

    protected function getCallerFromStackTrace()
    {
        $trace = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))->forget(0);

        return $trace->first(function ($frame) {
            if (! isset($frame['file'])) {
                return false;
            }

            return ! Str::contains($frame['file'], base_path('vendor'));
        });
    }
}