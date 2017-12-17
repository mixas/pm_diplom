<?php
/**
 * �������� ���� ������������ ��� ������ Application. ������ ������������ ��� ���������� �������� ��������� ����������,
 * �����������, ��������� ��������������, ������, ������������
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    /* ������������� �������� */
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
    /* ��������� ��������������� �������� ������������ � ������� ������ */
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
        ],
    ],
    /* ��������� ����������� ������� */
    'access_filter' => [
        'options' => [
            'mode' => 'restrictive'
        ],
        'controllers' => [
            Controller\IndexController::class => [
                // ��������� ���� �������� "index" , "about" , "help" ��������
                ['actions' => ['index', 'about', 'help'], 'allow' => '*'],
                // ��������� �������� "settings" ������ ������������������ ���������������
                ['actions' => ['settings'], 'allow' => '@']
            ],
        ]
    ],
    /* RBAC manager ��������� ��������� ���������� �������������.*/
    'rbac_manager' => [
        'assertions' => [Service\RbacAssertionManager::class],
    ],
    /* ��������� ��������������� �������� �������� � ������� ������, ������� - ��� ���� �� ����� ������� */
    'service_manager' => [
        'factories' => [
            Service\NavManager::class => Service\Factory\NavManagerFactory::class,
            Service\RbacAssertionManager::class => Service\Factory\RbacAssertionManagerFactory::class,
        ],
    ],
    /* ��������� ��������������� �������� view helper � ������� ������, ��� ��������������� ������� */
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
    /* ��������� ������� �������� */
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
    /* ��������� ��������� �����������(success � error ���������) */
    'view_helper_config' => [
        'flashmessenger' => [
            'message_open_format'      => '<div%s><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">x</a><li>',
            'message_close_string'     => '</li></div>',
            'message_separator_string' => '</li><li>'
        ]
    ],
];
