<?php

return [
    'environments' => ['production'],
    'backup' => [
        'listen' => true,
    ],
    'exceptions' => [
        'ignore' => [
            Illuminate\Foundation\Http\Exceptions\MaintenanceModeException::class,
        ]
    ]
];