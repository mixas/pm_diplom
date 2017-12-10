<?php
namespace User\Controller\Factory;

use Interop\Container\ContainerInterface;
use User\Controller\AuthController;
use Zend\ServiceManager\Factory\FactoryInterface;
use User\Service\AuthManager;
use User\Service\UserManager;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {   
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authManager = $container->get(AuthManager::class);
        $authService = $container->get(\Zend\Authentication\AuthenticationService::class);
        $userManager = $container->get(UserManager::class);
        
        return new AuthController($entityManager, $authManager, $authService, $userManager);
    }
}
