<?php

namespace Project;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Project\Entity\Task;

return [

    'controllers' => [
        'factories' => [
            Controller\ProjectController::class => Controller\Factory\ProjectControllerFactory::class,
            Controller\TaskController::class => Controller\Factory\TaskControllerFactory::class,
            Controller\TaskStatusController::class => Controller\Factory\TaskStatusControllerFactory::class,
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
            'tasks' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/tasks[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\TaskController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'statuses' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/statuses[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\TaskStatusController::class,
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
            Service\TaskManager::class => Service\Factory\TaskManagerFactory::class,
            Service\TaskStatusManager::class => Service\Factory\TaskStatusManagerFactory::class,
            'Project\Entity\Task' => 'Project\Entity\Factory\TaskFactory',
        ],
    ],
    'entity_manager' => [
        'factories' => [
            'Project\Entity\Task' => 'Project\Entity\Factory\TaskFactory',
        ],
    ],

];



