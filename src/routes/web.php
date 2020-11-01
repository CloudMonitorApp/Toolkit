<?php

use Illuminate\Http\Request;
use CloudMonitor\Toolkit\Ping;
use Illuminate\Support\Facades\Route;
use CloudMonitor\Toolkit\Logging\JavaScriptLogger;

Route::post('cloudmonitor', function(Request $request) {
    (new JavaScriptLogger)->write($request);
})->middleware('web');

Route::post('/cloudmonitor/callback', function(Request $request) {
    if ($request->header('x-key') !== md5(env('CLOUDMONITOR_KEY'))) {
        return;
    }
    
    if ($request->header('x-type') === 'ping') {
        Ping::send();
    }
});

Route::get('/js/cloudmonitor.js', function() {
    header('Content-Type: application/javascript');

    if(env('APP_DEV') !== true) {
        return file_get_contents(base_path('vendor/cloudmonitor/toolkit/src/resources/js/CloudMonitor.js'));
    }

    return file_get_contents(base_path('/CloudMonitorToolkit/src/resources/js/CloudMonitor.js'));
});