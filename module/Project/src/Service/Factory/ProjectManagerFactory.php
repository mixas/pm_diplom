<?php
namespace Project\Service\Factory;

use Interop\Container\ContainerInterface;
use Project\Service\ProjectManager;

/**
 * This is the factory class for UserManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class ProjectManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
                        
        return new ProjectManager($entityManager);
    }
}
