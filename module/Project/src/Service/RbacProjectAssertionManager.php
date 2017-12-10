<?php
namespace Project\Service;

use Zend\Permissions\Rbac\Rbac;
use User\Entity\User;

/**
 *  ласс дл€ проверки динамических разрешений дл€ пользователей на определенные дайстви€
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
     * ћетод используюетс€ дл€ динамической проверки разрешений действий пользователей
     */
    public function assert(Rbac $rbac, $permission, $params)
    {
        // “екущий пользователь
        $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());

        // ѕроверка принадлжность пользовател€ проекту
        if($permission=='projects.manage.own') {
            $userProjects = $currentUser->getProjects();
            $userProjects->initialize();
            foreach ($userProjects as $project) {
                if ($project->getId() == $params['project']->getId()) {
                    return true;
                }
            }
        }

        // ѕроверка на принадлежность комментари€ пользователю
        if($permission=='comments.manage.own') {
            if ($currentUser->getId() == $params['comment']->getUserId()) {
                return true;
            }
        }

        return false;
    }
}



