<?php
namespace User\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use User\Entity\User;

/**
 * ������ ����������� ����� �������� ������������ ������������
 */
class CurrentUserPlugin extends AbstractPlugin
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
    
    /**
     * Authentication service.
     * @var Zend\Authentication\AuthenticationService 
     */
    private $authService;
    
    /**
     * Logged in user.
     * @var User\Entity\User
     */
    private $user = null;
    
    public function __construct($entityManager, $authService)
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
    }

    /**
     * ����� ���������� ��� ������� ������� �������� ������������ $user = $this->currentUser();
     *
     * @param bool|true $useCachedUser
     * @return null|User\Entity\User
     * @throws \Exception
     */
    public function __invoke($useCachedUser = true)
    {        
        // ���� ������������ ��� ������, ������� ���.
        if ($useCachedUser && $this->user!==null)
            return $this->user;
        
        // �������� ��������������� �� ������������
        if ($this->authService->hasIdentity()) {
            
            // ������� User entity �� ��.
            $this->user = $this->entityManager->getRepository(User::class)
                    ->findOneByEmail($this->authService->getIdentity());
            if ($this->user==null) {
                throw new \Exception('Not found user with such email');
            }
            
            return $this->user;
        }
        
        return null;
    }
}



