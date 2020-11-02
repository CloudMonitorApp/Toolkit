<?php

return [
    'ip-source' => 'REMOTE_ADDR',
    'backup' => [
        'listen' => true,
    ],
    'exceptions' => [
        'ignore' => [
            Illuminate\Foundation\Http\Exceptions\MaintenanceModeException::class,
            Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        ]
    ],
    'suspicious' => [
        'operations' => [
            Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException::class,
            Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        ],
    ],
    'system' => [
        'monitor' => [
            'cpu',
            'ram',
        ],
    ],
];