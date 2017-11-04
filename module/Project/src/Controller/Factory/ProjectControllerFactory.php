<?php
namespace Project\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Project\Controller\ProjectController;
use Project\Service\ProjectManager;
use Project\Service\TechnicalAssignmentManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class ProjectControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $projectManager = $container->get(ProjectManager::class);
        $technicalAssignmentManager = $container->get(TechnicalAssignmentManager::class);

        // Instantiate the controller and inject dependencies
        return new ProjectController($entityManager, $projectManager, $technicalAssignmentManager);
    }
}