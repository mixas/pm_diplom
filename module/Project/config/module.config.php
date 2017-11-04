<?php

namespace Project;


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
            Controller\CommentController::class => Controller\Factory\CommentControllerFactory::class,
        ],
    ],

    // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthorized visitors.
    'access_filter' => [
        'options' => [
            // The access filter can work in 'restrictive' (recommended) or 'permissive'
            // mode. In restrictive mode all controller actions must be explicitly listed
            // under the 'access_filter' config key, and access is denied to any not listed
            // action for not logged in users. In permissive mode, if an action is not listed
            // under the 'access_filter' key, access to it is permitted to anyone (even for
            // not logged in users. Restrictive mode is more secure and recommended to use.
            'mode' => 'restrictive'
        ],
        //TODO:: We should add dynamic assertions for editPermissions action
        'controllers' => [
            Controller\ProjectController::class => [
                ['actions' => ['index', 'view', 'edit', 'add', 'createTechnicalAssignment', 'editTechnicalAssignment', 'viewTechnicalAssignment'], 'allow' => '+projects.view'],
                ['actions' => ['assignUsers'], 'allow' => '+projects.assign.users.all']
            ],
            Controller\TaskController::class => [
                ['actions' => ['index', 'view', 'edit', 'add'], 'allow' => '+projects.view'],
//                ['actions' => ['edit', 'add'], 'allow' => '+projects.manage.all']
            ],
            Controller\TaskStatusController::class => [
                ['actions' => ['index', 'view', 'edit', 'add'], 'allow' => '+status.manage'],
            ],
            Controller\CommentController::class => [
                ['actions' => ['index', 'edit', 'add'], 'allow' => '+projects.view'],
            ],
        ]
    ],

    // This key stores configuration for RBAC manager.
    'rbac_manager' => [
        'assertions' => [Service\RbacProjectAssertionManager::class],
    ],

    // This key stores configuration for RBAC manager.
    'router' => [
        'routes' => [
            'projects' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/projects[/:action[/:code]]',
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
                    'route'    => '/tasks[/:action[/:project[/:task]]]',
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
            'comments' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/comments[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\CommentController::class,
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
        'template_map' => [
            'comment' => __DIR__ . '/../view/project/task/comment.phtml',
        ]
    ],

    'service_manager' => [
        'factories' => [
            Service\ProjectManager::class => Service\Factory\ProjectManagerFactory::class,
            Service\TaskManager::class => Service\Factory\TaskManagerFactory::class,
            Service\TaskStatusManager::class => Service\Factory\TaskStatusManagerFactory::class,
            Service\CommentManager::class => Service\Factory\CommentManagerFactory::class,
            Service\RbacProjectAssertionManager::class => Service\Factory\RbacProjectAssertionManagerFactory::class,
            Service\TechnicalAssignmentManager::class => Service\Factory\TechnicalAssignmentManagerFactory::class,
        ],
    ],
    'entity_manager' => [
        'factories' => [
            'Project\Entity\Task' => 'Project\Entity\Factory\TaskFactory',
        ],
    ],

];



