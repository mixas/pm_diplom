<?php

namespace Project;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractActionController;
use Project\Controller\ProjectController;

class Module
{
    /**
     * This method returns the path to module.config.php file.
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

//    public function __construct($e, $sm) {
//        $app = $e->getParam('application');
//        $this->sm = $sm;
//        $em = $this->getEntityManager();
//    }

    public function onBootstrap(MvcEvent $e)
    {
        // This method is called once the MVC bootstrapping is complete
        $application = $e->getApplication();
        $serviceManager    = $application->getServiceManager();
    }

//    public function onBootstrap($e)
//    {
//        $serviceManager->get('viewhelpermanager')->setFactory('IsAuthz', function ($sm) use ($e) {
//            return new \xxx\View\Helper\IsAuthz($e, $sm);
//        });
//    }
}
