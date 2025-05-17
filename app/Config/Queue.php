<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Queue extends BaseConfig
{
    /**
     * Default queue driver
     *
     * @var string
     */
    public $driver = 'database';

    /**
     * Queue connection settings
     *
     * @var array
     */
    public $connections = [
        'database' => [
            'driver' => 'database',
            'table'  => 'queued_jobs',
            'connection' => '',
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'default',
            'retry_after' => 90,
        ],
        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'port' => 11300,
            'queue' => 'default',
            'retry_after' => 90,
        ],
    ];

    /**
     * Queue handlers mapping - associates job names with handler classes
     *
     * @var array
     */
    public $handlers = [
        'sendAsesmenEmail' => \App\Queue\EmailQueueHandler::class,
    ];

    /**
     * Default queue retry settings
     *
     * @var array
     */
    public $retry = [
        'max_attempts' => 3,
        'delay_seconds' => [
            1 => 60,    // 1st retry after 1 minute
            2 => 300,   // 2nd retry after 5 minutes
            3 => 1800,  // 3rd retry after 30 minutes
        ],
    ];

    /**
     * Failed job storage settings
     *
     * @var array
     */
    public $failed = [
        'driver' => 'database',
        'table'  => 'failed_jobs',
        'connection' => '',
    ];
}
