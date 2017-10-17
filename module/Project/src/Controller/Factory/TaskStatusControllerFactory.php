<?php

namespace Project\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Project\Controller\TaskStatusController;
use Project\Service\TaskStatusManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class TaskStatusControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $taskStatusManager = $container->get(TaskStatusManager::class);
        
        // Instantiate the controller and inject dependencies
        return new TaskStatusController($entityManager, $taskStatusManager);
    }
}