<?php

namespace Project\Service;

use Project\Entity\Task;
use Project\Entity\TaskStatus;
use User\Entity\User;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

use Interop\Container\ContainerInterface;

class SolutionProcessor
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Constructs the service.
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param $data
     * @return null
     */
    public function fetchTheBestUserSolution($data){
        $priority = $data['priority'];
        $userType = $data['user_type'];
        $result = [];
        try{
            $classesConformity = Task::getPriorityClassesConformity();
            $priorityClass = "Project\Service\PriorityProcessor\\" . $classesConformity[$priority];
            $priorityProcessor = new $priorityClass($this->entityManager);
            $bestUser = $priorityProcessor->process($userType);
            if($bestUser){
                $result['message'] = 'User was defined successfully';
                $result['success'] = true;
                $result['user'] = $bestUser;
            }else{
                throw new \Exception("User was not found");
            }
        }catch (\Exception $ex) {
            $result['message'] = $ex->getMessage();
            $result['success'] = false;
        }
        return $result;
    }

}

