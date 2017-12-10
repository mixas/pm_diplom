<?php
namespace User\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use User\Controller\RoleController;
use User\Service\RoleManager;
use Project\Service\TaskManager;

class RoleControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $roleManager = $container->get(RoleManager::class);
        $taskManager = $container->get(TaskManager::class);

        // Instantiate the controller and inject dependencies
        return new RoleController($entityManager, $roleManager, $taskManager);
    }
}

