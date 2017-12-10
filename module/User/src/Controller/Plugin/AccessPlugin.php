<?php
namespace User\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * ������ ��� �������� ����������: role-based access control (RBAC).
 */
class AccessPlugin extends AbstractPlugin
{
    private $rbacManager;
    
    public function __construct($rbacManager)
    {
        $this->rbacManager = $rbacManager;
    }
    
    /**
     * �������� ����� �� ������� ������������ ���������� �� ��������
     *
     * @param string $permission Permission name.
     * @param array $params
     */
    public function __invoke($permission, $params = [])
    {
        return $this->rbacManager->isGranted(null, $permission, $params);
    }
}


