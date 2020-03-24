<?php

use EmilMoe\CloudMonitor\Logging\JavaScriptLogger;
use Illuminate\Http\Request;

Route::post('cloudmonitor', function(Request $request) {
    (new JavaScriptLogger)->write($request);
})->middleware('web');

Route::get('/js/cloudmonitor.js', function() {
    header('Content-Type: application/javascript');
    return file_get_contents(base_path('/CloudMonitorToolkit/src/resources/js/CloudMonitor.js'));
});