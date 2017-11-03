<?php

namespace Project\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Project\Controller\CommentController;
use Project\Service\CommentManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class CommentControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $commentManager = $container->get(CommentManager::class);
        $rendererInterface = $container->get('Zend\View\Renderer\RendererInterface');
        $authService = $container->get(\Zend\Authentication\AuthenticationService::class);

        // Instantiate the controller and inject dependencies
        return new CommentController($entityManager, $commentManager, $rendererInterface, $authService);
    }
}