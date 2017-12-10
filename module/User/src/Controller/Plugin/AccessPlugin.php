<?php
namespace User\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * ѕлагин дл€ проверки полномочий: role-based access control (RBAC).
 */
class AccessPlugin extends AbstractPlugin
{
    private $rbacManager;
    
    public function __construct($rbacManager)
    {
        $this->rbacManager = $rbacManager;
    }
    
    /**
     * ѕроверка имеет ли текущий пользователь разрешение на действие
     *
     * @param string $permission Permission name.
     * @param array $params
     */
    public function __invoke($permission, $params = [])
    {
        return $this->rbacManager->isGranted(null, $permission, $params);
    }
}


