<?php

return [
    'ip-source' => 'REMOTE_ADDR',
    'backup' => [
        'listen' => true,
    ],
    'ignored_commands' => [
        'schedule:run',
        'schedule:finish',
        'package:discover',
        'vendor:publish',
        'package:discover',
        'migrate:rollback',
        'migrate:refresh',
        'migrate:fresh',
        'migrate:reset',
        'migrate:install',
        'config:cache',
        'config:clear',
        'route:cache',
        'list',
        'route:clear',
        'view:cache',
        'view:clear',
        'queue:listen',
        'queue:work',
        'queue:restart',
        'horizon',
        'horizon:work',
        'horizon:supervisor',
        'horizon:terminate',
        'horizon:snapshot',
        'nova:publish',
        'key:generate',
    ]
];