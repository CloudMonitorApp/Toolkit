<?php

return [
    'environments' => ['production'],
    'ip-source' => 'REMOTE_ADDR',
    'backup' => [
        'listen' => true,
    ],
    'exceptions' => [
        'ignore' => [
            Illuminate\Foundation\Http\Exceptions\MaintenanceModeException::class,
        ]
    ]
];