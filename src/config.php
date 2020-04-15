<?php

return [
    'environments' => ['production'],
    'exceptions' => [
        'ignore' => [
            Illuminate\Foundation\Http\Exceptions\MaintenanceModeException::class,
        ]
    ]
];