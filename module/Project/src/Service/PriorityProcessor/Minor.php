<?php

namespace Project\Service\PriorityProcessor;

use Project\Entity\Task;
use Project\Entity\TaskStatus;
use User\Entity\User;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

use Interop\Container\ContainerInterface;

class Minor extends PriorityAbstract
{

    public function process(){
        return 'minor!';
    }

}

