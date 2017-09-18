<?php

namespace Project;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
//    'controllers' => [
//        'factories' => [
//            Controller\ProjectController::class => function($container) {
//                $dbAdapter = $container->get('Zend\Db\Adapter\Adapter');
//                $resultSetPrototype = new ResultSet();
//                $tableGateway = new TableGateway('project', $dbAdapter, null, $resultSetPrototype);
//
//                return new Controller\ProjectController(
//                    new \Project\Model\ProjectTable($tableGateway)
//                );
//            },
//        ],
//    ],

    'controllers' => [
        'factories' => [
            Controller\ProjectController::class => Controller\Factory\ProjectControllerFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'projects' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/projects[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ProjectController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],
//    'access_filter' => [
//        'controllers' => [
//            Controller\ProjectController::class => [
//                // Give access to "resetPassword", "message" and "setPassword" actions
//                // to anyone.
//                ['actions' => ['resetPassword', 'message', 'setPassword'], 'allow' => '*'],
//                // Give access to "index", "add", "edit", "view", "changePassword" actions to authorized users only.
//                ['actions' => ['index', 'add', 'edit', 'view'], 'allow' => '@']
//            ],
//        ]
//    ],

    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],

    'view_manager' => [
        'template_path_stack' => [
            'test' => __DIR__ . '/../view',
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\ProjectManager::class => Service\Factory\ProjectManagerFactory::class,
        ],
    ],

];



