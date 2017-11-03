<?php
namespace Project\Service;

use Zend\Permissions\Rbac\Rbac;
use User\Entity\User;
use Project\Entity\Project;

/**
 * This service is used for invoking user-defined RBAC dynamic assertions.
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
    
    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $authService) 
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
    }
    
    /**
     * This method is used for dynamic assertions. 
     */
    public function assert(Rbac $rbac, $permission, $params)
    {
        $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());

        //check project manage permissions
        if($permission=='projects.manage.own') {
            $userProjects = $currentUser->getProjects();
            $userProjects->initialize();
            foreach ($userProjects as $project) {
                if ($project->getId() == $params['project']->getId()) {
                    return true;
                }
            }
        }

        //check comment manage permissions (only user who created comment is able to manage it)
        if($permission=='comments.manage.own') {
            if ($currentUser->getId() == $params['comment']->getUserId()) {
                return true;
            }
        }

        return false;
    }
}



