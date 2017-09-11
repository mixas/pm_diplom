<?php

namespace Test;

use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;


return [
    'controllers' => [
        'factories' => [
            Controller\TestController::class => function($container) {
                $dbAdapter = $container->get('Zend\Db\Adapter\Adapter');
                $resultSetPrototype = new ResultSet();
                $tableGateway = new TableGateway('test', $dbAdapter, null, $resultSetPrototype);

                return new Controller\TestController(
                    new \Test\Model\TestTable($tableGateway)
                );
            },
        ],
    ],

    'router' => [
        'routes' => [
            'test' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/test[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\TestController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'test' => __DIR__ . '/../view',
        ],
    ],
];