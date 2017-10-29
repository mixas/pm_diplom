<?php
namespace Application\Service;

/**
 * This service is responsible for determining which items should be in the main menu.
 * The items may be different depending on whether the user is authenticated or not.
 */
class NavManager
{
    /**
     * Auth service.
     * @var Zend\Authentication\Authentication
     */
    private $authService;
    
    /**
     * Url view helper.
     * @var Zend\View\Helper\Url
     */
    private $urlHelper;
    
    /**
     * RBAC manager.
     * @var User\Service\RbacManager
     */
    private $rbacManager;

    /**
     * Constructs the service.
     */
    public function __construct($authService, $urlHelper, $rbacManager)
    {
        $this->authService = $authService;
        $this->urlHelper = $urlHelper;
        $this->rbacManager = $rbacManager;
    }
    
    /**
     * This method returns menu items depending on whether user has logged in or not.
     */
    public function getMenuItems() 
    {
        $items = [];
        
        $items[] = [
            'id' => 'home',
            'label' => 'Home',
            'link'  => $this->urlHelper('home')
        ];
        
        $items[] = [
            'id' => 'about',
            'label' => 'About',
            'link'  => $this->urlHelper('about')
        ];

        $items[] = [
            'id' => 'projects',
            'label' => 'Projects',
            'link'  => $this->urlHelper('projects')
        ];

        $items[] = [
            'id' => 'tasks_menu',
            'label' => 'Tasks',
            'float' => 'left',
            'dropdown' => [
                [
                    'id' => 'tasks',
                    'label' => 'Assigned tasks',
                    'link' => $this->urlHelper('tasks')
                ],
                [
                    'id' => 'statuses',
                    'label' => 'Manage statuses',
                    'link' => $this->urlHelper('statuses')
                ],
            ]
        ];

        // Display "Login" menu item for not authorized user only. On the other hand,
        // display "Admin" and "Logout" menu items only for authorized users.
        if (!$this->authService->hasIdentity()) {
            $items[] = [
                'id' => 'login',
                'label' => 'Sign in',
                'link'  => $this->urlHelper('login'),
                'float' => 'right'
            ];
        } else {
            
            // Determine which items must be displayed in Admin dropdown.
            $adminDropdownItems = [];

            if ($this->rbacManager->isGranted(null, 'user.manage')) {
                $adminDropdownItems[] = [
                            'id' => 'users',
                            'label' => 'Manage Users',
                            'link' => $this->urlHelper('users')
                        ];
            }

            if ($this->rbacManager->isGranted(null, 'permission.manage')) {
                $adminDropdownItems[] = [
                            'id' => 'permissions',
                            'label' => 'Manage Permissions',
                            'link' => $this->urlHelper('permissions')
                        ];
            }

            if ($this->rbacManager->isGranted(null, 'role.manage')) {
                $adminDropdownItems[] = [
                            'id' => 'roles',
                            'label' => 'Manage Roles',
                            'link' => $this->urlHelper('roles')
                        ];
            }

            if (count($adminDropdownItems)!=0) {
                $items[] = [
                    'id' => 'admin',
                    'label' => 'Admin',
                    'dropdown' => $adminDropdownItems
                ];
            }
            
            $items[] = [
                'id' => 'logout',
                'label' => $this->authService->getIdentity(),
                'float' => 'right',
                'dropdown' => [
                    [
                        'id' => 'settings',
                        'label' => 'Settings',
                        'link' => $this->urlHelper('application', ['action'=>'settings'])
                    ],
                    [
                        'id' => 'logout',
                        'label' => 'Sign out',
                        'link' => $this->urlHelper('logout')
                    ],
                ]
            ];
        }
        
        return $items;
    }
}


