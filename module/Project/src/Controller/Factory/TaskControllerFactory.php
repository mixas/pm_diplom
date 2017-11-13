<?php

namespace Project\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Project\Controller\TaskController;
use Project\Service\TaskManager;
use Project\Service\TimeLogManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class TaskControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $taskManager = $container->get(TaskManager::class);
        $imeLogManager = $container->get(TimeLogManager::class);
        $authService = $container->get(\Zend\Authentication\AuthenticationService::class);
        $rendererInterface = $container->get('Zend\View\Renderer\RendererInterface');
        
        // Instantiate the controller and inject dependencies
        return new TaskController($entityManager, $taskManager, $imeLogManager, $authService, $rendererInterface);
    }
}