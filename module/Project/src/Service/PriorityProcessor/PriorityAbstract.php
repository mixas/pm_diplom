<?php

namespace Project\Service\PriorityProcessor;

use Project\Entity\Task;
use Project\Entity\TaskStatus;
use User\Entity\User;
use User\Entity\Role;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

use Interop\Container\ContainerInterface;

class PriorityAbstract
{

    protected $type;

    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @param $entityManager
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Метод должен быть реализован в наследующих классах
     * Реализация должна зависеть от типа(роли) пользователей
     *
     * @param $userType
     */
    public function process($userType){
        return;
    }

}

