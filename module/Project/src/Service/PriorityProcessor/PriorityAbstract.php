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

    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructs the service.
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process($userType){
        return 'abstract!';
    }

}

