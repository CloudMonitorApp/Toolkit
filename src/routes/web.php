<?php

use CloudMonitor\Toolkit\Logging\JavaScriptLogger;
use Illuminate\Http\Request;

Route::post('cloudmonitor', function(Request $request) {
    (new JavaScriptLogger)->write($request);
})->middleware('web');

Route::get('/js/cloudmonitor.js', function() {
    header('Content-Type: application/javascript');

    if(env('APP_DEV') !== true) {
        return file_get_contents(base_path('vendor/cloudmonitor/toolkit/src/resources/js/CloudMonitor.js'));
    }

    return file_get_contents(base_path('/CloudMonitorToolkit/src/resources/js/CloudMonitor.js'));
});