<?php

namespace Test;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use \Test\Model;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }


//    public function getControllerConfig()
//    {
//        return [
//            'factories' => [
//                Controller\TestController::class => function($container) {
//                    return new Controller\TestController(
//                        $container->get(Model\TestTable::class)
//                    );
//                },
//            ],
//        ];
//    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\TestTable::class => function($container) {
                    $tableGateway = $container->get(Model\TestTableGateway::class);
                    return new Model\TestTable($tableGateway);
                },
                Model\TestTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Test());
                    return new TableGateway('test', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

}