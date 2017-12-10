<?php
namespace User\Controller\Plugin\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use User\Service\RbacManager;
use User\Controller\Plugin\AccessPlugin;

class AccessPluginFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {   
        $rbacManager = $container->get(RbacManager::class);
        
        return new AccessPlugin($rbacManager);
    }
}


