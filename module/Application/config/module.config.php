<?php
/**
 * Основной файл конфигурации для модуля Application. Модуль предназначен для реализации основной структуры приложения,
 * регистрации, управлени пользователями, ролями, разрешениями
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    /* маршрутизация запросов */
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'about' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/about',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'about',
                    ],
                ],
            ],
        ],
    ],
    /* Настройка автоматического создания контроллеров с помощью фабрик */
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
        ],
    ],
    /* Настройка ограничений доступа */
    'access_filter' => [
        'options' => [
            'mode' => 'restrictive'
        ],
        'controllers' => [
            Controller\IndexController::class => [
                // разрешить всем посещать "index" , "about" , "help" страницы
                ['actions' => ['index', 'about', 'help'], 'allow' => '*'],
                // разрашить посещать "settings" только зарегистрированным пользовалетелям
                ['actions' => ['settings'], 'allow' => '@']
            ],
        ]
    ],
    /* RBAC manager позволяет проверять полномочия пользователей.*/
    'rbac_manager' => [
        'assertions' => [Service\RbacAssertionManager::class],
    ],
    /* Настройка автоматического создания сервисов с помощью фабрик, сервисы - это один из видов моделей */
    'service_manager' => [
        'factories' => [
            Service\NavManager::class => Service\Factory\NavManagerFactory::class,
            Service\RbacAssertionManager::class => Service\Factory\RbacAssertionManagerFactory::class,
        ],
    ],
    /* Настройка автоматического создания view helper с помощью фабрик, это вспомогательные функции */
    'view_helpers' => [
        'factories' => [
            View\Helper\Menu::class => View\Helper\Factory\MenuFactory::class,
            View\Helper\Breadcrumbs::class => InvokableFactory::class,
        ],
        'aliases' => [
            'mainMenu' => View\Helper\Menu::class,
            'pageBreadcrumbs' => View\Helper\Breadcrumbs::class,
        ],
    ],
    /* Настройка рендера шаблонов */
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    /* Настройка текстовых уведомлений(success и error сообщений) */
    'view_helper_config' => [
        'flashmessenger' => [
            'message_open_format'      => '<div%s><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">x</a><li>',
            'message_close_string'     => '</li></div>',
            'message_separator_string' => '</li><li>'
        ]
    ],
];
