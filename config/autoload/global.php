<?php
/**
 *
 */

use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;
use Doctrine\DBAL\Driver\PDOMySql\Driver as PDOMySqlDriver;

return [
    'db' => [
        'driver' => 'Pdo',
        'dsn'    => 'mysql:dbname=test;host=localhost;charset=utf8',
        'username' => 'root',
        'password' => '',

        'adapters' => [
            'Application\Db\WriteAdapter' => [
                'driver' => 'Pdo',
                'dsn'    => 'mysql:dbname=test;host=localhost;charset=utf8',
                'username' => 'root',
                'password' => '',
            ],
            'Application\Db\ReadOnlyAdapter' => [
                'driver' => 'Pdo',
                'dsn'    => 'mysql:dbname=test;host=localhost;charset=utf8',
                'username' => 'root',
                'password' => '',
            ],
        ],
    ],

    // Define Db connection settings for Doctrine module
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => PDOMySqlDriver::class,
                'params' => [
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'password' => '',
                    'dbname'   => 'test',
                ]
            ],
        ],
        'migrations_configuration' => [
            'orm_default' => [
                'directory' => 'data/Migrations',
                'name'      => 'Doctrine Database Migrations',
                'namespace' => 'Migrations',
                'table'     => 'migrations',
            ],
        ],
    ],



    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
            => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),

    // Session configuration.
    'session_config' => [
        'cookie_lifetime'     => 60*60*1, // Session cookie will expire in 1 hour.
        'gc_maxlifetime'      => 60*60*24*30, // How long to store session data on server (for 1 month).
    ],
    // Session manager configuration.
    'session_manager' => [
        // Session validators (used for security).
        'validators' => [
            RemoteAddr::class,
            HttpUserAgent::class,
        ]
    ],
    // Session storage configuration.
    'session_storage' => [
        'type' => SessionArrayStorage::class
    ],
];
