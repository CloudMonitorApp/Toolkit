<?php

use EmilMoe\CloudMonitor\Logging\JavaScriptLogger;
use Illuminate\Http\Request;

Route::post('cloudmonitor', function(Request $request) {
    (new JavaScriptLogger)->write($request);
});