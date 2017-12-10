<?php
namespace User\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use User\Entity\User;

/**
 * Плагин позволяющий брать текущего залогиненого пользователя
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
     * Метод вызывается при папытке извлечь текущего пользователя $user = $this->currentUser();
     *
     * @param bool|true $useCachedUser
     * @return null|User\Entity\User
     * @throws \Exception
     */
    public function __invoke($useCachedUser = true)
    {        
        // Если пользователь уже найден, вернуть его.
        if ($useCachedUser && $this->user!==null)
            return $this->user;
        
        // Проверка зарегистрирован ли пользователь
        if ($this->authService->hasIdentity()) {
            
            // извлечь User entity из БД.
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



