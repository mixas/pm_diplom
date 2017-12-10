<?php
namespace Project\Service;

use Zend\Permissions\Rbac\Rbac;
use User\Entity\User;

/**
 * ����� ��� �������� ������������ ���������� ��� ������������� �� ������������ ��������
 *
 * Class RbacProjectAssertionManager
 * @package Project\Service
 */
class RbacProjectAssertionManager
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
    
    /**
     * Auth service.
     * @var Zend\Authentication\AuthenticationService 
     */
    private $authService;
    
    public function __construct($entityManager, $authService)
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
    }
    
    /**
     * ����� ������������� ��� ������������ �������� ���������� �������� �������������
     */
    public function assert(Rbac $rbac, $permission, $params)
    {
        // ������� ������������
        $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());

        // �������� ������������� ������������ �������
        if($permission=='projects.manage.own') {
            $userProjects = $currentUser->getProjects();
            $userProjects->initialize();
            foreach ($userProjects as $project) {
                if ($project->getId() == $params['project']->getId()) {
                    return true;
                }
            }
        }

        // �������� �� �������������� ����������� ������������
        if($permission=='comments.manage.own') {
            if ($currentUser->getId() == $params['comment']->getUserId()) {
                return true;
            }
        }

        return false;
    }
}



