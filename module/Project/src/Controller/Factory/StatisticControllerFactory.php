<?php

namespace Project\Controller\Factory;

use Interop\Container\ContainerInterface;
use Project\Service\SolutionProcessor;
use Zend\ServiceManager\Factory\FactoryInterface;
use Project\Controller\StatisticController;
use Project\Service\TaskManager;
use Project\Service\StatisticManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class StatisticControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $taskManager = $container->get(TaskManager::class);
        $solutionProcessor = $container->get(SolutionProcessor::class);
        $statisticManager = $container->get(StatisticManager::class);
        $authService = $container->get(\Zend\Authentication\AuthenticationService::class);
        
        // Instantiate the controller and inject dependencies
        return new StatisticController($entityManager, $taskManager, $authService, $solutionProcessor, $statisticManager);
    }
}