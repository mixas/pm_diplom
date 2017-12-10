<?php
/**
 * Основной файл конфигурации для модуля Project модуля. Модуль предназначен для реализации управления проектами,
 * задачами, комментариями, а также основной логики приложения - расчета оптимального пользователя для выполнения
 * различных видом задач
 */
namespace Project;


use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    /* Настройка автоматического создания контроллеров с помощью фабрик */
    'controllers' => [
        'factories' => [
            Controller\ProjectController::class => Controller\Factory\ProjectControllerFactory::class,
            Controller\TaskController::class => Controller\Factory\TaskControllerFactory::class,
            Controller\TaskStatusController::class => Controller\Factory\TaskStatusControllerFactory::class,
            Controller\CommentController::class => Controller\Factory\CommentControllerFactory::class,
            Controller\SystemController::class => Controller\Factory\SystemControllerFactory::class,
            Controller\StatisticController::class => Controller\Factory\StatisticControllerFactory::class,
        ],
    ],

    /* Настройка ограничений доступа */
    'access_filter' => [
        'options' => [
            'mode' => 'restrictive'
        ],
        'controllers' => [
            Controller\ProjectController::class => [
                ['actions' => ['index', 'view', 'edit', 'add', 'delete', 'createTechnicalAssignment', 'editTechnicalAssignment', 'viewTechnicalAssignment', 'assignUsers'], 'allow' => '+projects.view'],
                ['actions' => ['delete'], 'allow' => '+projects.delete']
            ],
            Controller\TaskController::class => [
                ['actions' => ['index', 'view', 'edit', 'add', 'delete', 'reassign', 'addtimelog', 'edittimelog'], 'allow' => '+projects.view'],
            ],
            Controller\TaskStatusController::class => [
                ['actions' => ['index', 'view', 'edit', 'add', 'delete'], 'allow' => '+status.manage'],
            ],
            Controller\CommentController::class => [
                ['actions' => ['index', 'edit', 'add', 'delete'], 'allow' => '+projects.view'],
            ],
            Controller\SystemController::class => [
                ['actions' => ['chooseUserAutomatically', 'uploadFile', 'removeFile', 'downloadFile'], 'allow' => '+projects.view'],
            ],
            Controller\StatisticController::class => [
                ['actions' => ['index', 'users', 'projects'], 'allow' => '+stats.view'],
            ],
        ]
    ],

    /* RBAC manager позволяет проверять полномочия пользователей.*/
    'rbac_manager' => [
        'assertions' => [Service\RbacProjectAssertionManager::class],
    ],

    /* маршрутизация запросов */
    'router' => [
        'routes' => [
            'projects' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/projects[/:action[/:code]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'code' => '[a-zA-Z0-9_-]*',
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
            'system' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/system[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\SystemController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'stats' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/stats[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\StatisticController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],

    /* Секция для настройки doctrine (ORM, прослойка для работы с БД) */
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

    /* Настройка рендера шаблонов */
    'view_manager' => [
        'template_path_stack' => [
            'test' => __DIR__ . '/../view',
        ],
        'template_map' => [
            'comment' => __DIR__ . '/../view/project/task/comment.phtml',
            'all_users' => __DIR__ . '/../view/project/task/all-users.phtml',
            'time_log' => __DIR__ . '/../view/project/task/time-log.phtml',
            'spent_time' => __DIR__ . '/../view/project/task/spent-time.phtml',
            'technical_assignment_attachments' => __DIR__ . '/../view/project/project/attachment.phtml',
            'task_attachments' => __DIR__ . '/../view/project/task/attachment.phtml',
        ]
    ],

    /* Настройка автоматического создания сервисов с помощью фабрик, сервисы - это один из видов моделей */
    'service_manager' => [
        'factories' => [
            Service\ProjectManager::class => Service\Factory\ProjectManagerFactory::class,
            Service\TaskManager::class => Service\Factory\TaskManagerFactory::class,
            Service\TimeLogManager::class => Service\Factory\TimeLogManagerFactory::class,
            Service\TaskStatusManager::class => Service\Factory\TaskStatusManagerFactory::class,
            Service\CommentManager::class => Service\Factory\CommentManagerFactory::class,
            Service\RbacProjectAssertionManager::class => Service\Factory\RbacProjectAssertionManagerFactory::class,
            Service\TechnicalAssignmentManager::class => Service\Factory\TechnicalAssignmentManagerFactory::class,
            Service\SolutionProcessor::class => Service\Factory\SolutionProcessorFactory::class,
            Service\StatisticManager::class => Service\Factory\StatisticManagerFactory::class,
            Service\AttachmentManager::class => Service\Factory\AttachmentManagerFactory::class,
            Service\PriorityProcessor\PriorityAbstract::class => Service\PriorityProcessor\Factory\PriorityAbstractFactory::class,
        ],
    ],

    /* Настройка автоматического создания view helper с помощью фабрик, это вспомогательные функции */
    'view_helpers' => [
        'factories' => [
            View\Helper\Project::class => InvokableFactory::class,
        ],
        'aliases' => [
            'projectHelper' => View\Helper\Project::class,
        ],
    ],
];



